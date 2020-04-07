<?php

$exceptionTemplate = <<<EOF
<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * %exceptionClassName% used for %errorCode% EBICS error
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class %exceptionClassName% extends EbicsResponseException
{
    public function __construct(?string \$responseMessage = null)
    {
        parent::__construct('%errorCode%', \$responseMessage, '%errorMeaning%');
    }
}

EOF;

$exceptionsDestinationDirectory = __DIR__ . '/../src/Exceptions';
$errorCodesFile = fopen(__DIR__ . '/datas/error_codes.csv', 'r');
$exceptionMapping = [];
while (false !== ($datas = fgetcsv($errorCodesFile, 4096, ";"))) {
    $exceptionClassName = sprintf('%sException', camelize(str_replace('EBICS_', '', $datas[1])));
    $exceptionCode = str_pad($datas[0], 6, STR_PAD_LEFT, '0');
    $exceptionTemplateContent = str_replace('%exceptionClassName%', $exceptionClassName, $exceptionTemplate);
    $exceptionTemplateContent = str_replace('%errorCode%', $exceptionCode, $exceptionTemplateContent);
    $exceptionTemplateContent = str_replace('%errorMeaning%', escapeArgument($datas[2]), $exceptionTemplateContent);

    file_put_contents(sprintf('%s/%s.php', $exceptionsDestinationDirectory, $exceptionClassName), $exceptionTemplateContent);
    $exceptionMapping[] = sprintf("'%s' => %s::class", $exceptionCode, $exceptionClassName);
}

fclose($errorCodesFile);


$exceptionMappingTemplate = <<<EOF
<?php

namespace AndrewSvirin\Ebics\Exceptions;

/**
 * Mapping class between error code and exception classes. @see \AndrewSvirin\Ebics\Factories\EbicsExceptionFactory
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
abstract class EbicsErrorCodeMapping
{
    public static \$mapping = [
        %exceptionMapping%
    ];
}

EOF;

$exceptionMappingContent = str_replace('%exceptionMapping%', implode(",\n\t\t", $exceptionMapping), $exceptionMappingTemplate);
file_put_contents(sprintf('%s/EbicsErrorCodeMapping.php', $exceptionsDestinationDirectory), $exceptionMappingContent);

function camelize($input, $separator = '_')
{
    return str_replace($separator, '', ucwords(strtolower($input), $separator));
}

function escapeArgument($input)
{
    return str_replace("'", "\'", $input);
}