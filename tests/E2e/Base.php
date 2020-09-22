<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\E2e;

use AndrewSvirin\Ebics\EbicsClient;
use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\BodyHandler;
use AndrewSvirin\Ebics\Handlers\EbicsRequestHandler;
use AndrewSvirin\Ebics\Handlers\HeaderHandler;
use AndrewSvirin\Ebics\Handlers\HostHandler;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Version;
use AndrewSvirin\Ebics\RequestMaker;
use AndrewSvirin\Ebics\Services\CryptService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use XmlValidator\XmlValidator;

use function print_r;
use function str_repeat;

/**
 * @coversNothing
 */
class Base extends TestCase
{
    public function getSut(string $requestExpected, string $fakeReponse, string $version): EbicsClient
    {
        X509GeneratorFactory::setGeneratorClass(PredictableX509::class);

        $callback = static function ($method, $url, $options) use ($fakeReponse, $requestExpected, $version) {
            self::assertXmlStringEqualsXmlString($requestExpected, $options['body']);

            $versionToXsd = [
                Version::V24 => __DIR__ . '/xsd/24/H003/ebics.xsd',
                Version::V25 => __DIR__ . '/xsd/25/ebics_H004.xsd',
                Version::V30 => __DIR__ . '/xsd/30/ebics_H005.xsd',
            ];

            $xmlValidator = new XmlValidator();
            $isValid      = $xmlValidator->validate($requestExpected, $versionToXsd[$version]);

            self::assertTrue($isValid, print_r($xmlValidator->errors, true));

            $xmlValidator = new XmlValidator();
            $isValid      = $xmlValidator->validate($fakeReponse, $versionToXsd[$version]);

            self::assertTrue($isValid, print_r($xmlValidator->errors, true));

            return new MockResponse($fakeReponse);
        };

        $httpClient = new MockHttpClient($callback);

        $cryptService = self::createMock(CryptService::class);
        $cryptService->expects(self::any())->method('getPublicKeyDetails')->willReturn(['m' => 'test1', 'e' => 'test2']);
        $cryptService->expects(self::any())->method('generateKeys')->willReturn(['publickey' => 'test1', 'privatekey' => 'test2']);
        $cryptService->expects(self::any())->method('getPublicKeyDetails')->willReturn(['m' => 'test1', 'e' => 'test2']);
        $cryptService->expects(self::any())->method('calculateDigest')->willReturn('test');
        $cryptService->expects(self::any())->method('generateNonce')->willReturn(str_repeat('A', 32));
        $cryptService->expects(self::any())->method('decryptOrderData')->willReturn(new OrderData());

        return new EbicsClient(
            new RequestMaker($httpClient),
            new RequestHandler(
                new EbicsRequestHandler(),
                new HeaderHandler($cryptService),
                new BodyHandler(),
                new OrderDataHandler(
                    null,
                    $cryptService
                ),
                new AuthSignatureHandler(),
                new HostHandler()
            ),
            new ResponseHandler(),
            $cryptService,
            new CertificateFactory()
        );
    }
}
