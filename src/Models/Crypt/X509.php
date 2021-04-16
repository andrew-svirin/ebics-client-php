<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\ASN1Interface;
use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;
use AndrewSvirin\Ebics\Contracts\Crypt\X509Interface;
use DateTime;
use DateTimeZone;
use LogicException;

/**
 * Pure-PHP X.509 Parser
 *
 * Encode and decode X.509 certificates.
 *
 * The extensions are from {@link http://tools.ietf.org/html/rfc5280 RFC5280} and
 * {@link http://web.archive.org/web/19961027104704/http://www3.netscape.com/eng/security/cert-exts.html
 * Netscape Certificate Extensions}.
 *
 * Note that loading an X.509 certificate and resaving it may invalidate the signature.
 * The reason being that the signature is based on a portion of the certificate that
 * contains optional parameters with default values.  ie. if the parameter isn't there
 * the default value is used.  Problem is, if the parameter is there and it just so happens
 * to have the default value there are two ways that that parameter can be encoded.  It can
 * be encoded explicitly or left out all together.  This would effect the signature value
 * and thus may invalidate the the certificate all together unless the certificate is re-signed.
 */
class X509 implements X509Interface
{

    /**#@+*/
    /**
     * Save as DER
     */
    const FORMAT_DER = 1;
    /**
     * Auto-detect the format
     *
     * Used only by the load*() functions
     */
    const FORMAT_AUTO_DETECT = 3;
    /**#@-*/

    /**
     * Attribute value disposition.
     * If disposition is >= 0, this is the index of the target value.
     */
    const ATTR_ALL = -1; // All attribute values (array).
    const ATTR_APPEND = -2; // Add a value.
    const ATTR_REPLACE = -3; // Clear first, then add a value.

    /**#@+*/
    /**
     * Return internal array representation
     */
    const DN_ARRAY = 0;
    /**
     * Return string
     */
    const DN_STRING = 1;
    /**
     * Return canonical ASN.1 RDNs string
     */
    const DN_CANON = 4;
    /**#@-*/

    /**#@+
     * ASN.1 syntax for various extensions
     */
    /**
     * @var array
     */
    protected $DirectoryString;

    /**
     * @var array
     */
    protected $PKCS9String;

    /**
     * @var array
     */
    protected $AttributeValue;

    /**
     * @var array
     */
    protected $Extensions;

    /**
     * @var array
     */
    protected $KeyUsage;

    /**
     * @var array
     */
    protected $ExtKeyUsageSyntax;

    /**
     * @var array
     */
    protected $BasicConstraints;

    /**
     * @var array
     */
    protected $KeyIdentifier;

    /**
     * @var array
     */
    protected $AuthorityKeyIdentifier;

    /**
     * @var array
     */
    protected $CertificatePolicies;

    /**
     * @var array
     */
    protected $SubjectAltName;

    /**
     * @var array
     */
    protected $Name;

    /**
     * @var array
     */
    protected $RelativeDistinguishedName;

    /**
     * @var array
     */
    protected $InvalidityDate;

    /**
     * ASN.1 syntax for X.509 certificates
     *
     * @var array
     */
    protected $Certificate;

    /**
     * Public key
     *
     * @var RSAInterface|null
     */
    protected $publicKey;

    /**
     * Private key
     *
     * @var RSAInterface|null
     */
    protected $privateKey;

    /**
     * The currently loaded certificate
     *
     * @var array|null
     */
    protected $currentCert;

    /**
     * Certificate Start Date
     *
     * @var string
     */
    protected $startDate;

    /**
     * Certificate End Date
     *
     * @var string
     */
    protected $endDate;

    /**
     * Serial Number
     *
     * @var string
     */
    protected $serialNumber;

    /**
     * @var array
     */
    protected $domains;

    /**
     * The signature subject
     *
     * There's no guarantee X509 is going to re-encode an X.509 cert in the same way it was originally
     * encoded so we take save the portion of the original cert that the signature would have made for.
     *
     * @var string
     */
    protected $signatureSubject;

    /**
     * Distinguished Name
     *
     * @var array|null
     */
    protected $dn;

    /**
     * Object identifiers for X.509 certificates
     *
     * @var array
     * @link http://en.wikipedia.org/wiki/Object_identifier
     */
    protected $oids;

    /**
     * Key Identifier
     *
     * See {@link http://tools.ietf.org/html/rfc5280#section-4.2.1.1 RFC5280#section-4.2.1.1} and
     * {@link http://tools.ietf.org/html/rfc5280#section-4.2.1.2 RFC5280#section-4.2.1.2}.
     *
     * @var string
     */
    protected $currentKeyIdentifier;

    /**
     * Default Constructor.
     */
    public function __construct()
    {
        // Explicitly Tagged Module, 1988 Syntax
        // http://tools.ietf.org/html/rfc5280#appendix-A.1

        $this->DirectoryString = [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'teletexString' => ['type' => ASN1::TYPE_TELETEX_STRING],
                'printableString' => ['type' => ASN1::TYPE_PRINTABLE_STRING],
                'universalString' => ['type' => ASN1::TYPE_UNIVERSAL_STRING],
                'utf8String' => ['type' => ASN1::TYPE_UTF8_STRING],
                'bmpString' => ['type' => ASN1::TYPE_BMP_STRING]
            ]
        ];

        $this->PKCS9String = [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'ia5String' => ['type' => ASN1::TYPE_IA5_STRING],
                'directoryString' => $this->DirectoryString
            ]
        ];

        $this->AttributeValue = ['type' => ASN1::TYPE_ANY];

        $AttributeType = ['type' => ASN1::TYPE_OBJECT_IDENTIFIER];

        $AttributeTypeAndValue = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'type' => $AttributeType,
                'value' => $this->AttributeValue
            ]
        ];

        /*
        In practice, RDNs containing multiple name-value pairs (called "multivalued RDNs") are rare,
        but they can be useful at times when either there is no unique attribute in the entry or you
        want to ensure that the entry's DN contains some useful identifying information.

        - https://www.opends.org/wiki/page/DefinitionRelativeDistinguishedName
        */
        $this->RelativeDistinguishedName = [
            'type' => ASN1::TYPE_SET,
            'min' => 1,
            'max' => -1,
            'children' => $AttributeTypeAndValue
        ];

        // http://tools.ietf.org/html/rfc5280#section-4.1.2.4
        $RDNSequence = [
            'type' => ASN1::TYPE_SEQUENCE,
            // RDNSequence does not define a min or a max, which means it doesn't have one
            'min' => 0,
            'max' => -1,
            'children' => $this->RelativeDistinguishedName
        ];

        $this->Name = [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'rdnSequence' => $RDNSequence
            ]
        ];

        // http://tools.ietf.org/html/rfc5280#section-4.1.1.2
        $AlgorithmIdentifier = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'algorithm' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'parameters' => [
                    'type' => ASN1::TYPE_ANY,
                    'optional' => true
                ]
            ]
        ];

        /*
           A certificate using system MUST reject the certificate if it encounters
           a critical extension it does not recognize; however, a non-critical
           extension may be ignored if it is not recognized.

           http://tools.ietf.org/html/rfc5280#section-4.2
        */
        $Extension = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'extnId' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'critical' => [
                    'type' => ASN1::TYPE_BOOLEAN,
                    'optional' => true,
                    'default' => false
                ],
                'extnValue' => ['type' => ASN1::TYPE_OCTET_STRING]
            ]
        ];

        $this->Extensions = [
            'type' => ASN1::TYPE_SEQUENCE,
            'min' => 1,
            // technically, it's MAX, but we'll assume anything < 0 is MAX
            'max' => -1,
            // if 'children' isn't an array then 'min' and 'max' must be defined
            'children' => $Extension
        ];

        $SubjectPublicKeyInfo = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'algorithm' => $AlgorithmIdentifier,
                'subjectPublicKey' => ['type' => ASN1::TYPE_BIT_STRING]
            ]
        ];

        $UniqueIdentifier = ['type' => ASN1::TYPE_BIT_STRING];

        $Time = [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'utcTime' => ['type' => ASN1::TYPE_UTC_TIME],
                'generalTime' => ['type' => ASN1::TYPE_GENERALIZED_TIME]
            ]
        ];

        // http://tools.ietf.org/html/rfc5280#section-4.1.2.5
        $Validity = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'notBefore' => $Time,
                'notAfter' => $Time
            ]
        ];

        $CertificateSerialNumber = ['type' => ASN1::TYPE_INTEGER];

        $Version = [
            'type' => ASN1::TYPE_INTEGER,
            'mapping' => ['v1', 'v2', 'v3']
        ];

        // assert($TBSCertificate['children']['signature'] == $Certificate['children']['signatureAlgorithm'])
        $TBSCertificate = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                // technically, default implies optional, but we'll define it as being optional, none-the-less, just to
                // reenforce that fact
                'version' => [
                        'constant' => 0,
                        'optional' => true,
                        'explicit' => true,
                        'default' => 'v1'
                    ] + $Version,
                'serialNumber' => $CertificateSerialNumber,
                'signature' => $AlgorithmIdentifier,
                'issuer' => $this->Name,
                'validity' => $Validity,
                'subject' => $this->Name,
                'subjectPublicKeyInfo' => $SubjectPublicKeyInfo,
                // implicit means that the T in the TLV structure is to be rewritten, regardless of the type
                'issuerUniqueID' => [
                        'constant' => 1,
                        'optional' => true,
                        'implicit' => true
                    ] + $UniqueIdentifier,
                'subjectUniqueID' => [
                        'constant' => 2,
                        'optional' => true,
                        'implicit' => true
                    ] + $UniqueIdentifier,
                // <http://tools.ietf.org/html/rfc2459#page-74> doesn't use the EXPLICIT keyword but if
                // it's not IMPLICIT, it's EXPLICIT
                'extensions' => [
                        'constant' => 3,
                        'optional' => true,
                        'explicit' => true
                    ] + $this->Extensions
            ]
        ];

        $this->Certificate = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'tbsCertificate' => $TBSCertificate,
                'signatureAlgorithm' => $AlgorithmIdentifier,
                'signature' => ['type' => ASN1::TYPE_BIT_STRING]
            ]
        ];

        $this->KeyUsage = [
            'type' => ASN1::TYPE_BIT_STRING,
            'mapping' => [
                'digitalSignature',
                'nonRepudiation',
                'keyEncipherment',
                'dataEncipherment',
                'keyAgreement',
                'keyCertSign',
                'cRLSign',
                'encipherOnly',
                'decipherOnly'
            ]
        ];

        $this->BasicConstraints = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'cA' => [
                    'type' => ASN1::TYPE_BOOLEAN,
                    'optional' => true,
                    'default' => false
                ],
                'pathLenConstraint' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'optional' => true
                ]
            ]
        ];

        $this->KeyIdentifier = ['type' => ASN1::TYPE_OCTET_STRING];

        $OrganizationalUnitNames = [
            'type' => ASN1::TYPE_SEQUENCE,
            'min' => 1,
            'max' => 4, // ub-organizational-units
            'children' => ['type' => ASN1::TYPE_PRINTABLE_STRING]
        ];

        $PersonalName = [
            'type' => ASN1::TYPE_SET,
            'children' => [
                'surname' => [
                    'type' => ASN1::TYPE_PRINTABLE_STRING,
                    'constant' => 0,
                    'optional' => true,
                    'implicit' => true
                ],
                'given-name' => [
                    'type' => ASN1::TYPE_PRINTABLE_STRING,
                    'constant' => 1,
                    'optional' => true,
                    'implicit' => true
                ],
                'initials' => [
                    'type' => ASN1::TYPE_PRINTABLE_STRING,
                    'constant' => 2,
                    'optional' => true,
                    'implicit' => true
                ],
                'generation-qualifier' => [
                    'type' => ASN1::TYPE_PRINTABLE_STRING,
                    'constant' => 3,
                    'optional' => true,
                    'implicit' => true
                ]
            ]
        ];

        $NumericUserIdentifier = ['type' => ASN1::TYPE_NUMERIC_STRING];

        $OrganizationName = ['type' => ASN1::TYPE_PRINTABLE_STRING];

        $PrivateDomainName = [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'numeric' => ['type' => ASN1::TYPE_NUMERIC_STRING],
                'printable' => ['type' => ASN1::TYPE_PRINTABLE_STRING]
            ]
        ];

        $TerminalIdentifier = ['type' => ASN1::TYPE_PRINTABLE_STRING];

        $NetworkAddress = ['type' => ASN1::TYPE_NUMERIC_STRING];

        $AdministrationDomainName = [
            'type' => ASN1::TYPE_CHOICE,
            // if class isn't present it's assumed to be ASN1::CLASS_UNIVERSAL or
            // (if constant is present) ASN1::CLASS_CONTEXT_SPECIFIC
            'class' => ASN1::CLASS_APPLICATION,
            'cast' => 2,
            'children' => [
                'numeric' => ['type' => ASN1::TYPE_NUMERIC_STRING],
                'printable' => ['type' => ASN1::TYPE_PRINTABLE_STRING]
            ]
        ];

        $CountryName = [
            'type' => ASN1::TYPE_CHOICE,
            // if class isn't present it's assumed to be ASN1::CLASS_UNIVERSAL or
            // (if constant is present) ASN1::CLASS_CONTEXT_SPECIFIC
            'class' => ASN1::CLASS_APPLICATION,
            'cast' => 1,
            'children' => [
                'x121-dcc-code' => ['type' => ASN1::TYPE_NUMERIC_STRING],
                'iso-3166-alpha2-code' => ['type' => ASN1::TYPE_PRINTABLE_STRING]
            ]
        ];

        $AnotherName = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'type-id' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'value' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 0,
                    'optional' => true,
                    'explicit' => true
                ]
            ]
        ];

        $ExtensionAttribute = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'extension-attribute-type' => [
                    'type' => ASN1::TYPE_PRINTABLE_STRING,
                    'constant' => 0,
                    'optional' => true,
                    'implicit' => true
                ],
                'extension-attribute-value' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 1,
                    'optional' => true,
                    'explicit' => true
                ]
            ]
        ];

        $ExtensionAttributes = [
            'type' => ASN1::TYPE_SET,
            'min' => 1,
            'max' => 256, // ub-extension-attributes
            'children' => $ExtensionAttribute
        ];

        $BuiltInDomainDefinedAttribute = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'type' => ['type' => ASN1::TYPE_PRINTABLE_STRING],
                'value' => ['type' => ASN1::TYPE_PRINTABLE_STRING]
            ]
        ];

        $BuiltInDomainDefinedAttributes = [
            'type' => ASN1::TYPE_SEQUENCE,
            'min' => 1,
            'max' => 4, // ub-domain-defined-attributes
            'children' => $BuiltInDomainDefinedAttribute
        ];

        $BuiltInStandardAttributes = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'country-name' => ['optional' => true] + $CountryName,
                'administration-domain-name' => ['optional' => true] + $AdministrationDomainName,
                'network-address' => [
                        'constant' => 0,
                        'optional' => true,
                        'implicit' => true
                    ] + $NetworkAddress,
                'terminal-identifier' => [
                        'constant' => 1,
                        'optional' => true,
                        'implicit' => true
                    ] + $TerminalIdentifier,
                'private-domain-name' => [
                        'constant' => 2,
                        'optional' => true,
                        'explicit' => true
                    ] + $PrivateDomainName,
                'organization-name' => [
                        'constant' => 3,
                        'optional' => true,
                        'implicit' => true
                    ] + $OrganizationName,
                'numeric-user-identifier' => [
                        'constant' => 4,
                        'optional' => true,
                        'implicit' => true
                    ] + $NumericUserIdentifier,
                'personal-name' => [
                        'constant' => 5,
                        'optional' => true,
                        'implicit' => true
                    ] + $PersonalName,
                'organizational-unit-names' => [
                        'constant' => 6,
                        'optional' => true,
                        'implicit' => true
                    ] + $OrganizationalUnitNames
            ]
        ];

        $ORAddress = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'built-in-standard-attributes' => $BuiltInStandardAttributes,
                'built-in-domain-defined-attributes' => ['optional' => true] + $BuiltInDomainDefinedAttributes,
                'extension-attributes' => ['optional' => true] + $ExtensionAttributes
            ]
        ];

        $EDIPartyName = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'nameAssigner' => [
                        'constant' => 0,
                        'optional' => true,
                        'implicit' => true
                    ] + $this->DirectoryString,
                // partyName is technically required but ASN1 doesn't currently support non-optional constants and
                // setting it to optional gets the job done in any event.
                'partyName' => [
                        'constant' => 1,
                        'optional' => true,
                        'implicit' => true
                    ] + $this->DirectoryString
            ]
        ];

        $GeneralName = [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'otherName' => [
                        'constant' => 0,
                        'optional' => true,
                        'implicit' => true
                    ] + $AnotherName,
                'rfc822Name' => [
                    'type' => ASN1::TYPE_IA5_STRING,
                    'constant' => 1,
                    'optional' => true,
                    'implicit' => true
                ],
                'dNSName' => [
                    'type' => ASN1::TYPE_IA5_STRING,
                    'constant' => 2,
                    'optional' => true,
                    'implicit' => true
                ],
                'x400Address' => [
                        'constant' => 3,
                        'optional' => true,
                        'implicit' => true
                    ] + $ORAddress,
                'directoryName' => [
                        'constant' => 4,
                        'optional' => true,
                        'explicit' => true
                    ] + $this->Name,
                'ediPartyName' => [
                        'constant' => 5,
                        'optional' => true,
                        'implicit' => true
                    ] + $EDIPartyName,
                'uniformResourceIdentifier' => [
                    'type' => ASN1::TYPE_IA5_STRING,
                    'constant' => 6,
                    'optional' => true,
                    'implicit' => true
                ],
                'iPAddress' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                    'constant' => 7,
                    'optional' => true,
                    'implicit' => true
                ],
                'registeredID' => [
                    'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                    'constant' => 8,
                    'optional' => true,
                    'implicit' => true
                ]
            ]
        ];

        $GeneralNames = [
            'type' => ASN1::TYPE_SEQUENCE,
            'min' => 1,
            'max' => -1,
            'children' => $GeneralName
        ];

        $this->AuthorityKeyIdentifier = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'keyIdentifier' => [
                        'constant' => 0,
                        'optional' => true,
                        'implicit' => true
                    ] + $this->KeyIdentifier,
                'authorityCertIssuer' => [
                        'constant' => 1,
                        'optional' => true,
                        'implicit' => true
                    ] + $GeneralNames,
                'authorityCertSerialNumber' => [
                        'constant' => 2,
                        'optional' => true,
                        'implicit' => true
                    ] + $CertificateSerialNumber
            ]
        ];

        $PolicyQualifierId = ['type' => ASN1::TYPE_OBJECT_IDENTIFIER];

        $PolicyQualifierInfo = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'policyQualifierId' => $PolicyQualifierId,
                'qualifier' => ['type' => ASN1::TYPE_ANY]
            ]
        ];

        $CertPolicyId = ['type' => ASN1::TYPE_OBJECT_IDENTIFIER];

        $PolicyInformation = [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'policyIdentifier' => $CertPolicyId,
                'policyQualifiers' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'min' => 0,
                    'max' => -1,
                    'optional' => true,
                    'children' => $PolicyQualifierInfo
                ]
            ]
        ];

        $this->CertificatePolicies = [
            'type' => ASN1::TYPE_SEQUENCE,
            'min' => 1,
            'max' => -1,
            'children' => $PolicyInformation
        ];

        $KeyPurposeId = ['type' => ASN1::TYPE_OBJECT_IDENTIFIER];

        $this->ExtKeyUsageSyntax = [
            'type' => ASN1::TYPE_SEQUENCE,
            'min' => 1,
            'max' => -1,
            'children' => $KeyPurposeId
        ];

        $this->SubjectAltName = $GeneralNames;

        $this->InvalidityDate = ['type' => ASN1::TYPE_GENERALIZED_TIME];

        // OIDs from RFC5280 and those RFCs mentioned in RFC5280#section-4.1.1.2
        $this->oids = [
            '2.5.4' => 'id-at',
            '2.5.4.41' => 'id-at-name',
            '2.5.4.4' => 'id-at-surname',
            '2.5.4.42' => 'id-at-givenName',
            '2.5.4.43' => 'id-at-initials',
            '2.5.4.44' => 'id-at-generationQualifier',
            '2.5.4.3' => 'id-at-commonName',
            '2.5.4.7' => 'id-at-localityName',
            '2.5.4.8' => 'id-at-stateOrProvinceName',
            '2.5.4.10' => 'id-at-organizationName',
            '2.5.4.11' => 'id-at-organizationalUnitName',
            '2.5.4.12' => 'id-at-title',
            '2.5.4.13' => 'id-at-description',
            '2.5.4.46' => 'id-at-dnQualifier',
            '2.5.4.6' => 'id-at-countryName',
            '2.5.4.5' => 'id-at-serialNumber',
            '2.5.4.65' => 'id-at-pseudonym',
            '2.5.4.17' => 'id-at-postalCode',
            '2.5.4.9' => 'id-at-streetAddress',
            '2.5.4.45' => 'id-at-uniqueIdentifier',
            '2.5.4.72' => 'id-at-role',
            '2.5.4.16' => 'id-at-postalAddress',

            '0.9.2342.19200300.100.1.25' => 'id-domainComponent',
            '2.5.29' => 'id-ce',
            '2.5.29.35' => 'id-ce-authorityKeyIdentifier',
            '2.5.29.14' => 'id-ce-subjectKeyIdentifier',
            '2.5.29.15' => 'id-ce-keyUsage',
            '2.5.29.16' => 'id-ce-privateKeyUsagePeriod',
            '2.5.29.32' => 'id-ce-certificatePolicies',
            '2.5.29.32.0' => 'anyPolicy',

            '2.5.29.33' => 'id-ce-policyMappings',
            '2.5.29.17' => 'id-ce-subjectAltName',
            '2.5.29.18' => 'id-ce-issuerAltName',
            '2.5.29.9' => 'id-ce-subjectDirectoryAttributes',
            '2.5.29.19' => 'id-ce-basicConstraints',
            '2.5.29.30' => 'id-ce-nameConstraints',
            '2.5.29.36' => 'id-ce-policyConstraints',
            '2.5.29.31' => 'id-ce-cRLDistributionPoints',
            '2.5.29.37' => 'id-ce-extKeyUsage',
            '2.5.29.37.0' => 'anyExtendedKeyUsage',
            '1.3.6.1.5.5.7.3.1' => 'id-kp-serverAuth',
            '1.3.6.1.5.5.7.3.2' => 'id-kp-clientAuth',
            '1.3.6.1.5.5.7.3.3' => 'id-kp-codeSigning',
            '1.3.6.1.5.5.7.3.4' => 'id-kp-emailProtection',
            '1.3.6.1.5.5.7.3.8' => 'id-kp-timeStamping',
            '1.3.6.1.5.5.7.1.1' => 'id-pe-authorityInfoAccess',
            '1.3.6.1.5.5.7.1.11' => 'id-pe-subjectInfoAccess',

            '1.2.840.113549.1.1.1' => 'rsaEncryption',
            '1.2.840.113549.1.1.11' => 'sha256WithRSAEncryption',
        ];
    }

    public function saveX509CurrentCert()
    {
        return $this->saveX509($this->currentCert);
    }

    public function setStartDate($date)
    {
        $date = new DateTime($date, new DateTimeZone(@date_default_timezone_get()));

        $this->startDate = $date->format('D, d M Y H:i:s O');
    }

    public function setEndDate($date)
    {
        $date = new DateTime($date, new DateTimeZone(@date_default_timezone_get()));

        $this->endDate = $date->format('D, d M Y H:i:s O');
    }

    public function setSerialNumber($serial, $base = -256)
    {
        $this->serialNumber = new BigInteger($serial, $base);
    }

    public function sign(
        $issuer,
        $subject,
        $signatureAlgorithm = 'sha1WithRSAEncryption'
    ) {
        if (empty($issuer->getPrivateKey()) || empty($issuer->getDN())) {
            return false;
        }

        $subjectPublicKey = $subject->formatSubjectPublicKey();

        $currentCert = isset($this->currentCert) ? $this->currentCert : null;
        $signatureSubject = isset($this->signatureSubject) ? $this->signatureSubject : null;

        if (isset($subject->currentCert) &&
            is_array($subject->currentCert) &&
            isset($subject->currentCert['tbsCertificate'])) {
            $this->currentCert = $subject->currentCert;
            $this->currentCert['tbsCertificate']['signature']['algorithm'] = $signatureAlgorithm;
            $this->currentCert['signatureAlgorithm']['algorithm'] = $signatureAlgorithm;

            if (!empty($this->startDate)) {
                $this->currentCert['tbsCertificate']['validity']['notBefore'] = $this->timeField($this->startDate);
            }
            if (!empty($this->endDate)) {
                $this->currentCert['tbsCertificate']['validity']['notAfter'] = $this->timeField($this->endDate);
            }
            if (!empty($this->serialNumber)) {
                $this->currentCert['tbsCertificate']['serialNumber'] = $this->serialNumber;
            }
            if (!empty($subject->dn)) {
                $this->currentCert['tbsCertificate']['subject'] = $subject->getDN();
            }
            if (!empty($subject->publicKey)) {
                $this->currentCert['tbsCertificate']['subjectPublicKeyInfo'] = $subjectPublicKey;
            }
            $this->removeExtension('id-ce-authorityKeyIdentifier');
            if (isset($subject->domains)) {
                $this->removeExtension('id-ce-subjectAltName');
            }
        } elseif (isset($subject->currentCert) &&
            is_array($subject->currentCert) &&
            isset($subject->currentCert['tbsCertList'])) {
            return false;
        } else {
            if (!isset($subject->publicKey)) {
                return false;
            }

            $startDate = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));
            $startDate = !empty($this->startDate) ? $this->startDate : $startDate->format('D, d M Y H:i:s O');

            $endDate = new DateTime('+1 year', new DateTimeZone(@date_default_timezone_get()));
            $endDate = !empty($this->endDate) ? $this->endDate : $endDate->format('D, d M Y H:i:s O');

            /* "The serial number MUST be a positive integer"
               "Conforming CAs MUST NOT use serialNumber values longer than 20 octets."
                -- https://tools.ietf.org/html/rfc5280#section-4.1.2.2

               for the integer to be positive the leading bit needs to be 0 hence the
               application of a bitmap
            */
            if (empty($this->serialNumber)) {
                throw new LogicException('Serial number must be defined.');
            }
            $serialNumber = $this->serialNumber;

            $this->currentCert = [
                'tbsCertificate' =>
                    [
                        'version' => 'v3',
                        'serialNumber' => $serialNumber, // $this->setSerialNumber()
                        'signature' => ['algorithm' => $signatureAlgorithm],
                        'issuer' => false, // this is going to be overwritten later
                        'validity' => [
                            'notBefore' => $this->timeField($startDate), // $this->setStartDate()
                            'notAfter' => $this->timeField($endDate)   // $this->setEndDate()
                        ],
                        'subject' => $subject->getDN(),
                        'subjectPublicKeyInfo' => $subjectPublicKey
                    ],
                'signatureAlgorithm' => ['algorithm' => $signatureAlgorithm],
                'signature' => false // this is going to be overwritten later
            ];

            // Copy extensions from CSR.
            $csrexts = $subject->getAttribute('pkcs-9-at-extensionRequest', 0);

            if (!empty($csrexts)) {
                $this->currentCert['tbsCertificate']['extensions'] = $csrexts;
            }
        }

        $this->currentCert['tbsCertificate']['issuer'] = $issuer->getDN();

        if (isset($issuer->currentKeyIdentifier)) {
            $this->setExtension('id-ce-authorityKeyIdentifier', [
                'keyIdentifier' => $issuer->currentKeyIdentifier
            ]);
        }

        if (isset($subject->currentKeyIdentifier)) {
            $this->setExtension('id-ce-subjectKeyIdentifier', $subject->currentKeyIdentifier);
        }

        $altName = [];

        if (isset($subject->domains) && count($subject->domains)) {
            $altName = array_map([X509::class, 'dnsName'], $subject->domains);
        }

        if (isset($subject->ipAddresses) && count($subject->ipAddresses)) {
            throw new LogicException('Subject IP address is not supported.');
        }

        if (!empty($altName)) {
            $this->setExtension('id-ce-subjectAltName', $altName);
        }

        // resync $this->signatureSubject
        $tbsCertificate = $this->currentCert['tbsCertificate'];
        $this->loadX509($this->saveX509($this->currentCert));

        $result = $this->signByKey($issuer->getPrivateKey(), $signatureAlgorithm);
        $result['tbsCertificate'] = $tbsCertificate;

        $this->currentCert = $currentCert;
        $this->signatureSubject = $signatureSubject;

        return $result;
    }

    public function loadX509($cert)
    {
        $asn1 = new ASN1();

        if ($cert !== false) {
            $newcert = $this->extractBER($cert);
            $cert = $newcert;
        }

        if ($cert === false) {
            $this->currentCert = null;
            return false;
        }

        $asn1->loadOIDs($this->oids);
        $decoded = $asn1->decodeBER($cert);

        if (!empty($decoded)) {
            $x509 = $asn1->asn1map($decoded[0], $this->Certificate);
        }
        if (!isset($x509) || $x509 === false) {
            $this->currentCert = null;
            return false;
        }

        $this->signatureSubject = substr(
            $cert,
            $decoded[0]['content'][0]['start'],
            $decoded[0]['content'][0]['length']
        );

        if (is_array($x509) && $this->isSubArrayValid($x509, 'tbsCertificate/extensions')) {
            $this->mapInExtensions($x509, 'tbsCertificate/extensions', $asn1);
        }

        $key = &$x509['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey'];
        $key = $this->reformatKey($x509['tbsCertificate']['subjectPublicKeyInfo']['algorithm']['algorithm'], $key);

        $this->currentCert = $x509;
        $this->dn = $x509['tbsCertificate']['subject'];

        $currentKeyIdentifier = $this->getExtension('id-ce-subjectKeyIdentifier');
        $this->currentKeyIdentifier = is_string($currentKeyIdentifier) ? $currentKeyIdentifier : null;

        return $x509;
    }

    /**
     * Remove an Extension
     *
     * @param string $id
     * @param string|null $path optional
     *
     * @return bool
     */
    private function removeExtension(string $id, string $path = null)
    {
        $extensions = &$this->extensions($this->currentCert, $path);

        if (!is_array($extensions)) {
            return false;
        }

        $result = false;
        foreach ($extensions as $key => $value) {
            if ($value['extnId'] == $id) {
                unset($extensions[$key]);
                $result = true;
            }
        }

        $extensions = array_values($extensions);
        // fix for https://bugs.php.net/75433 affecting PHP 7.2
        if (!isset($extensions[0])) {
            $extensions = array_splice($extensions, 0, 0);
        }
        return $result;
    }

    public function saveX509($cert)
    {
        if (!is_array($cert) || !isset($cert['tbsCertificate'])) {
            return false;
        }

        switch (true) {
            // "case !$a: case !$b: break; default: whatever();" is the same thing as "if ($a && $b) whatever()"
            case !($algorithm = $this->subArray($cert, 'tbsCertificate/subjectPublicKeyInfo/algorithm/algorithm')):
            case is_object($cert['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey']):
                break;
            default:
                switch ($algorithm) {
                    case 'rsaEncryption':
                        $cert['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey']
                            = base64_encode("\0" . base64_decode(preg_replace(
                                '#-.+-|[\r\n]#',
                                '',
                                $cert['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey']
                            )));
                        /* "[For RSA keys] the parameters field MUST have ASN.1 type NULL for this
                           algorithm identifier."
                           -- https://tools.ietf.org/html/rfc3279#section-2.3.1

                           given that and the fact that RSA keys appear ot be the only key type for
                           which the parameters field can be blank, it seems like perhaps the ASN.1
                           description ought not say the parameters field is OPTIONAL, but whatever.
                         */
                        $cert['tbsCertificate']['subjectPublicKeyInfo']['algorithm']['parameters'] = null;
                        // https://tools.ietf.org/html/rfc3279#section-2.2.1
                        $cert['signatureAlgorithm']['parameters'] = null;
                        $cert['tbsCertificate']['signature']['parameters'] = null;
                }
        }

        $asn1 = new ASN1();
        $asn1->loadOIDs($this->oids);

        $filters = [];
        $type_utf8_string = ['type' => ASN1::TYPE_UTF8_STRING];
        $filters['tbsCertificate']['signature']['parameters'] = $type_utf8_string;
        $filters['tbsCertificate']['signature']['issuer']['rdnSequence']['value'] = $type_utf8_string;
        $filters['tbsCertificate']['issuer']['rdnSequence']['value'] = $type_utf8_string;
        $filters['tbsCertificate']['subject']['rdnSequence']['value'] = $type_utf8_string;
        $filters['tbsCertificate']['subjectPublicKeyInfo']['algorithm']['parameters'] = $type_utf8_string;
        $filters['signatureAlgorithm']['parameters'] = $type_utf8_string;
        $filters['authorityCertIssuer']['directoryName']['rdnSequence']['value'] = $type_utf8_string;
        //$filters['policyQualifiers']['qualifier'] = $type_utf8_string;
        $filters['distributionPoint']['fullName']['directoryName']['rdnSequence']['value'] = $type_utf8_string;
        $filters['directoryName']['rdnSequence']['value'] = $type_utf8_string;

        /* in the case of policyQualifiers/qualifier, the type has to be ASN1::TYPE_IA5_STRING.
           ASN1::TYPE_PRINTABLE_STRING will cause OpenSSL's X.509 parser to spit out random
           characters.
         */
        $filters['policyQualifiers']['qualifier'] = ['type' => ASN1::TYPE_IA5_STRING];

        $asn1->loadFilters($filters);

        $this->mapOutExtensions($cert, 'tbsCertificate/extensions', $asn1);

        $cert = $asn1->encodeDER($cert, $this->Certificate);

        return "-----BEGIN CERTIFICATE-----\r\n" . chunk_split(base64_encode($cert), 64) .
            '-----END CERTIFICATE-----';
    }

    public function setPublicKey($key)
    {
        $key->setPublicKey();
        $this->publicKey = $key;
    }

    public function getPublicKey(): ?RSAInterface
    {
        return $this->publicKey;
    }

    public function setPrivateKey($key)
    {
        $this->privateKey = $key;
    }

    public function getPrivateKey(): ?RSAInterface
    {
        return $this->privateKey;
    }

    public function setDN($dn, $type = 'utf8String')
    {
        $this->dn = null;

        if (is_array($dn)) {
            if (isset($dn['rdnSequence'])) {
                $this->dn = $dn; // No merge here.
                return true;
            }

            // handles stuff generated by openssl_x509_parse()
            foreach ($dn as $prop => $value) {
                if (!$this->setDNProp($prop, $value, $type)) {
                    return false;
                }
            }
            return true;
        }

        // handles everything else
        $results = preg_split('#((?:^|, *|/)(?:C=|O=|OU=|CN=|L=|ST=|SN=|postalCode=|streetAddress=|' .
            'emailAddress=|serialNumber=|organizationalUnitName=|title=|description=|role=|' .
            'x500UniqueIdentifier=|postalAddress=))#', $dn, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (!is_array($results)) {
            throw new LogicException('Split result must be an array.');
        }

        for ($i = 1; $i < count($results); $i += 2) {
            $prop = trim($results[$i], ', =/');
            $value = $results[$i + 1];
            if (!$this->setDNProp($prop, $value, $type)) {
                return false;
            }
        }

        return true;
    }

    public function getDN()
    {
        return is_array($this->currentCert) && isset($this->currentCert['tbsCertList']) ?
            $this->currentCert['tbsCertList']['issuer'] : $this->dn;
    }

    public function setDomain()
    {
        $this->domains = func_get_args();
        $this->removeDNProp('id-at-commonName');
        $this->setDNProp('id-at-commonName', $this->domains[0]);
    }

    public function setKeyIdentifier($value)
    {
        if (empty($value)) {
            unset($this->currentKeyIdentifier);
        } else {
            $this->currentKeyIdentifier = base64_encode($value);
        }
    }

    public function computeKeyIdentifier($key = null)
    {
        if (is_null($key)) {
            $key = $this;
        }

        switch (true) {
            case $key instanceof RSAInterface:
                $key = $key->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
                break;
            default:
                throw new LogicException('Key type incorrect.');
        }

        // If in PEM format, convert to binary.
        $key = $this->extractBER($key);

        // Now we have the key string: compute its sha-1 sum.
        $hash = new Hash('sha1');
        $hash = $hash->hash($key);

        return $hash;
    }

    public function formatSubjectPublicKey(): ?array
    {
        if ($this->publicKey instanceof RSAInterface) {
            // the following two return statements do the same thing. i dunno.. i just prefer the later for some reason.
            // the former is a good example of how to do fuzzing on the public key
            return [
                'algorithm' => ['algorithm' => 'rsaEncryption'],
                'subjectPublicKey' => $this->publicKey->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1)
            ];
        }

        return null;
    }

    /**
     * X.509 certificate signing helper function.
     *
     * @param RSAInterface $key
     * @param string $signatureAlgorithm
     *
     * @return array
     */
    private function signByKey(RSAInterface $key, string $signatureAlgorithm)
    {
        switch ($signatureAlgorithm) {
            case 'sha256WithRSAEncryption':
                $key->setHash(preg_replace('#WithRSAEncryption$#', '', $signatureAlgorithm));

                $this->currentCert['signature'] = base64_encode("\0" . $key->sign($this->signatureSubject));
                return $this->currentCert;
            default:
                throw new LogicException('signatureAlgorithm not defined,');
        }
    }

    /**
     * Helper function to build a time field according to RFC 3280 section
     *  - 4.1.2.5 Validity
     *  - 5.1.2.4 This Update
     *  - 5.1.2.5 Next Update
     *  - 5.1.2.6 Revoked Certificates
     * by choosing utcTime iff year of date given is before 2050 and generalTime else.
     *
     * @param string $date in format date('D, d M Y H:i:s O')
     *
     * @return array
     */
    private function timeField(string $date)
    {
        $dateObj = new DateTime($date, new DateTimeZone('GMT'));
        $year = $dateObj->format('Y'); // the same way ASN1.php parses this
        if ($year < 2050) {
            return ['utcTime' => $date];
        } else {
            return ['generalTime' => $date];
        }
    }

    public function getAttribute($id, $disposition = self::ATTR_ALL, $csr = null)
    {
        if (empty($csr)) {
            $csr = $this->currentCert;
        }

        $attributes = $this->subArray($csr, 'certificationRequestInfo/attributes');

        if (!is_array($attributes)) {
            return false;
        }

        foreach ($attributes as $attribute) {
            if ($attribute['type'] == $id) {
                $n = count($attribute['value']);
                switch (true) {
                    case $disposition == self::ATTR_APPEND:
                    case $disposition == self::ATTR_REPLACE:
                        return false;
                    case $disposition == self::ATTR_ALL:
                        return $attribute['value'];
                    case $disposition >= $n:
                        $disposition -= $n;
                        break;
                    default:
                        return $attribute['value'][$disposition];
                }
            }
        }

        return false;
    }

    /**
     * Get a reference to a subarray
     *
     * @param array $root
     * @param string $path absolute path with / as component separator
     * @param bool $create optional
     *
     * @return array|false
     */
    private function &subArray(&$root, string $path, bool $create = false)
    {
        $false = false;

        if (!is_array($root)) {
            return $false;
        }

        foreach (explode('/', $path) as $i) {
            if (!is_array($root)) {
                return $false;
            }

            if (!isset($root[$i])) {
                if (!$create) {
                    return $false;
                }

                $root[$i] = [];
            }

            $root = &$root[$i];
        }

        return $root;
    }

    /**
     * Extract raw BER from Base64 encoding
     *
     * @param string $str
     *
     * @return string
     */
    private function extractBER(string $str)
    {
        /* X.509 certs are assumed to be base64 encoded but sometimes they'll have additional things in them
         * above and beyond the ceritificate.
         * ie. some may have the following preceding the -----BEGIN CERTIFICATE----- line:
         *
         * Bag Attributes
         *     localKeyID: 01 00 00 00
         * subject=/O=organization/OU=org unit/CN=common name
         * issuer=/O=organization/CN=common name
         */
        $temp = strlen($str) <= ini_get('pcre.backtrack_limit') ?
            preg_replace('#.*?^-+[^-]+-+[\r\n ]*$#ms', '', $str, 1) :
            $str;
        // remove new lines
        $temp = str_replace(["\r", "\n", ' '], '', $temp);
        // remove the -----BEGIN CERTIFICATE----- and -----END CERTIFICATE----- stuff
        $temp = preg_replace('#^-+[^-]+-+|-+[^-]+-+$#', '', $temp);
        $temp = preg_match('#^[a-zA-Z\d/+]*={0,2}$#', $temp) ? base64_decode($temp) : false;
        return $temp != false ? $temp : $str;
    }

    /**
     * Check for validity of subarray
     *
     * This is intended for use in conjunction with _subArrayUnchecked(),
     * implementing the checks included in _subArray() but without copying
     * a potentially large array by passing its reference by-value to is_array().
     *
     * @param array $root
     * @param string $path
     *
     * @return boolean
     */
    private function isSubArrayValid($root, string $path)
    {
        if (!is_array($root)) {
            return false;
        }

        foreach (explode('/', $path) as $i) {
            if (!is_array($root)) {
                return false;
            }

            if (!isset($root[$i])) {
                return true;
            }

            $root = $root[$i];
        }

        return true;
    }

    /**
     * Map extension values from octet string to extension-specific internal
     *   format.
     *
     * @param array $root (by reference)
     * @param string $path
     * @param ASN1Interface $asn1
     *
     * @return void
     */
    private function mapInExtensions(&$root, string $path, ASN1Interface $asn1)
    {
        $extensions = &$this->subArrayUnchecked($root, $path);

        if ($extensions) {
            for ($i = 0; $i < count($extensions); $i++) {
                $id = $extensions[$i]['extnId'];
                $value = &$extensions[$i]['extnValue'];
                $value = base64_decode($value);
                $decoded = $asn1->decodeBER($value);
                /* [extnValue] contains the DER encoding of an ASN.1 value
                   corresponding to the extension type identified by extnID */
                $map = $this->getMapping($id);
                if (!is_bool($map)) {
                    $decoder = $id == 'id-ce-nameConstraints' ?
                        [$this, '_decodeNameConstraintIP'] :
                        [$this, '_decodeIP'];
                    $mapped = $asn1->asn1map($decoded[0], $map, ['iPAddress' => $decoder]);
                    $value = $mapped === false ? $decoded[0] : $mapped;
                } else {
                    $value = base64_encode($value);
                }
            }
        }
    }

    /**
     * Reformat public keys
     *
     * Reformats a public key to a format supported by lib (if applicable)
     *
     * @param string $algorithm
     * @param string $key
     *
     * @return string
     */
    private function reformatKey(string $algorithm, string $key)
    {
        switch ($algorithm) {
            case 'rsaEncryption':
                return
                    "-----BEGIN RSA PUBLIC KEY-----\r\n" .
                    // subjectPublicKey is stored as a bit string in X.509 certs.  the first byte of
                    // a bit string represents how many bits in the last byte should be ignored.  the
                    // following only supports non-zero stuff but as none of the X.509 certs Firefox
                    // uses as a cert authority actually use a non-zero bit I think it's safe to assume
                    // that none do.
                    chunk_split(base64_encode(substr(base64_decode($key), 1)), 64) .
                    '-----END RSA PUBLIC KEY-----';
            default:
                return $key;
        }
    }

    public function setExtension($id, $value, $critical = false, $replace = true, string $path = null)
    {
        $extensions = &$this->extensions($this->currentCert, $path, true);

        if (!is_array($extensions)) {
            return false;
        }

        $newext = ['extnId' => $id, 'critical' => $critical, 'extnValue' => $value];

        foreach ($extensions as $key => $value) {
            if ($value['extnId'] == $id) {
                if (!$replace) {
                    return false;
                }

                $extensions[$key] = $newext;
                return true;
            }
        }

        $extensions[] = $newext;
        return true;
    }

    /**
     * Get a reference to an extension subarray
     *
     * @param array|null $root
     * @param string|null $path optional absolute path with / as component separator
     * @param bool $create optional
     *
     * @return array|false
     */
    private function &extensions(&$root, string $path = null, bool $create = false)
    {
        if (!isset($root)) {
            $root = $this->currentCert;
        }

        switch (true) {
            case !empty($path):
            case !is_array($root):
                break;
            case isset($root['tbsCertificate']):
                $path = 'tbsCertificate/extensions';
                break;
        }

        $extensions = &$this->subArray($root, $path, $create);

        if (!is_array($extensions)) {
            $false = false;
            return $false;
        }

        return $extensions;
    }

    /**
     * Map extension values from extension-specific internal format to
     *   octet string.
     *
     * @param array $root (by reference)
     * @param string $path
     * @param ASN1Interface $asn1
     *
     * @return void
     */
    private function mapOutExtensions(&$root, string $path, ASN1Interface $asn1)
    {
        $extensions = &$this->subArray($root, $path);

        if (is_array($extensions)) {
            $size = count($extensions);
            for ($i = 0; $i < $size; $i++) {
                $id = $extensions[$i]['extnId'];
                $value = &$extensions[$i]['extnValue'];

                switch ($id) {
                    case 'id-ce-certificatePolicies':
                        throw new LogicException('id-ce-certificatePolicies not handled.');
                    case 'id-ce-authorityKeyIdentifier': // use 00 as the serial number instead of an empty string
                        if (isset($value['authorityCertSerialNumber'])) {
                            if ($value['authorityCertSerialNumber']->toBytes() == '') {
                                throw new LogicException('authorityCertSerialNumber can not be empty.');
                            }
                        }
                }

                /* [extnValue] contains the DER encoding of an ASN.1 value
                   corresponding to the extension type identified by extnID */
                $map = $this->getMapping($id);
                if (false === $map) {
                    throw new LogicException($id . ' is not a currently supported extension');
                } else {
                    $temp = $asn1->encodeDER($value, $map, ['iPAddress' => [$this, '_encodeIP']]);
                    $value = base64_encode($temp);
                }
            }
        }
    }

    /**
     * Associate an extension ID to an extension mapping
     *
     * @param string $extnId
     *
     * @return array|false
     */
    private function getMapping(string $extnId)
    {
        switch ($extnId) {
            case 'id-ce-keyUsage':
                return $this->KeyUsage;
            case 'id-ce-basicConstraints':
                return $this->BasicConstraints;
            case 'id-ce-subjectKeyIdentifier':
                return $this->KeyIdentifier;
            case 'id-ce-authorityKeyIdentifier':
                return $this->AuthorityKeyIdentifier;
            case 'id-ce-certificatePolicies':
                return $this->CertificatePolicies;
            case 'id-ce-extKeyUsage':
                return $this->ExtKeyUsageSyntax;
            case 'id-ce-subjectAltName':
                return $this->SubjectAltName;
            case 'id-ce-invalidityDate':
                return $this->InvalidityDate;
        }

        return false;
    }

    /**
     * Get a reference to a subarray
     *
     * This variant of _subArray() does no is_array() checking,
     * so $root should be checked with _isSubArrayValid() first.
     *
     * This is here for performance reasons:
     * Passing a reference (i.e. $root) by-value (i.e. to is_array())
     * creates a copy. If $root is an especially large array, this is expensive.
     *
     * @param array $root
     * @param string $path absolute path with / as component separator
     * @param bool $create optional
     *
     * @return array|false
     */
    private function &subArrayUnchecked(&$root, string $path, bool $create = false)
    {
        $false = false;

        foreach (explode('/', $path) as $i) {
            if (!isset($root[$i])) {
                if (!$create) {
                    return $false;
                }

                $root[$i] = [];
            }

            $root = &$root[$i];
        }

        return $root;
    }

    /**
     * Set a Distinguished Name property
     *
     * @param string $propName
     * @param mixed $propValue
     * @param string $type optional
     *
     * @return bool
     */
    private function setDNProp(string $propName, $propValue, string $type = 'utf8String')
    {
        if (empty($this->dn)) {
            $this->dn = ['rdnSequence' => []];
        }

        if (($propName = $this->translateDNProp($propName)) === false) {
            return false;
        }

        foreach ((array)$propValue as $v) {
            if (!is_array($v)) {
                $v = [$type => $v];
            }
            $this->dn['rdnSequence'][] = [
                [
                    'type' => $propName,
                    'value' => $v
                ]
            ];
        }

        return true;
    }

    /**
     * "Normalizes" a Distinguished Name property
     *
     * @param string $propName
     *
     * @return mixed
     */
    private function translateDNProp(string $propName)
    {
        switch (strtolower($propName)) {
            case 'id-at-countryname':
            case 'countryname':
            case 'c':
                return 'id-at-countryName';
            case 'id-at-organizationname':
            case 'organizationname':
            case 'o':
                return 'id-at-organizationName';
            case 'id-at-commonname':
            case 'commonname':
            case 'cn':
                return 'id-at-commonName';
            case 'id-at-stateorprovincename':
            case 'stateorprovincename':
            case 'state':
            case 'province':
            case 'provincename':
            case 'st':
                return 'id-at-stateOrProvinceName';
            case 'id-at-localityname':
            case 'localityname':
            case 'l':
                return 'id-at-localityName';
            default:
                return false;
        }
    }

    /**
     * Get a certificate, CSR or CRL Extension
     *
     * Returns the extension if it exists and false if not
     *
     * @param string $id
     * @param array|null $cert optional
     *
     * @return mixed
     */
    private function getExtension(string $id, array $cert = null)
    {
        $extensions = $this->extensions($cert);

        if (!is_array($extensions)) {
            return false;
        }

        foreach ($extensions as $value) {
            if ($value['extnId'] == $id) {
                return $value['extnValue'];
            }
        }

        return false;
    }

    /**
     * Remove Distinguished Name properties
     *
     * @param string $propName
     *
     * @return void
     */
    private function removeDNProp(string $propName)
    {
        if (empty($this->dn)) {
            return;
        }

        if (($propName = $this->translateDNProp($propName)) === false) {
            return;
        }

        $dn = &$this->dn['rdnSequence'];
        $size = count($dn);
        for ($i = 0; $i < $size; $i++) {
            if ($dn[$i][0]['type'] == $propName) {
                unset($dn[$i]);
            }
        }

        $dn = array_values($dn);
        // fix for https://bugs.php.net/75433 affecting PHP 7.2
        if (!isset($dn[0])) {
            $dn = array_splice($dn, 0, 0);
        }
    }

    public function getIssuerDNProp($propName, $withType = false)
    {
        switch (true) {
            case !isset($this->currentCert) || !is_array($this->currentCert):
                break;
            case isset($this->currentCert['tbsCertificate']):
                return $this->getDNProp($propName, $this->currentCert['tbsCertificate']['issuer'], $withType);
            case isset($this->currentCert['tbsCertList']):
                return $this->getDNProp($propName, $this->currentCert['tbsCertList']['issuer'], $withType);
        }

        return false;
    }

    /**
     * Get Distinguished Name properties
     *
     * @param string $propName
     * @param array|null $dn optional
     * @param bool $withType optional
     *
     * @return mixed
     */
    private function getDNProp(string $propName, array $dn = null, bool $withType = false)
    {
        if (!isset($dn)) {
            $dn = $this->dn;
        }

        if (empty($dn)) {
            return false;
        }

        if (($propName = $this->translateDNProp($propName)) === false) {
            return false;
        }

        $asn1 = new ASN1();
        $asn1->loadOIDs($this->oids);
        $filters = [];
        $filters['value'] = ['type' => ASN1::TYPE_UTF8_STRING];
        $asn1->loadFilters($filters);

        $dn = $dn['rdnSequence'];
        $result = [];
        for ($i = 0; $i < count($dn); $i++) {
            if ($dn[$i][0]['type'] == $propName) {
                $v = $dn[$i][0]['value'];
                if (!$withType) {
                    if (is_array($v)) {
                        foreach ($v as $type => $s) {
                            $type = array_search($type, $asn1->getANYmap(), true);
                            if ($type !== false && isset($asn1->getStringTypeSize()[$type])) {
                                $s = $asn1->convert($s, (int)$type);
                                if ($s !== false) {
                                    $v = $s;
                                    break;
                                }
                            }
                        }
                        if (is_array($v)) {
                            $v = array_pop($v); // Always strip data type.
                        }
                    } else {
                        throw new LogicException('Value is not an array.');
                    }
                }
                $result[] = $v;
            }
        }

        return $result;
    }

    /**
     * Helper function to build domain array
     *
     * @param string $domain
     *
     * @return array
     */
    private function dnsName(string $domain)
    {
        return ['dNSName' => $domain];
    }
}
