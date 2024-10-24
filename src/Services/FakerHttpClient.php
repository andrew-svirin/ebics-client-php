<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\HttpClientInterface;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use LogicException;

/**
 * Class FakerHttpClient.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class FakerHttpClient implements HttpClientInterface
{
    /**
     * @var string
     */
    private $fixturesDir;

    public function __construct(string $fixturesDir)
    {
        $this->fixturesDir = $fixturesDir;
    }

    public function post(string $url, Request $request): Response
    {
        $requestContent = $request->getContent();

        $orderTypeMatches = [];
        $orderTypeMatch = preg_match(
            '/(<OrderType>|<AdminOrderType>)(?<order_type>.*)(<\/AdminOrderType>|<\/OrderType>)/',
            $requestContent,
            $orderTypeMatches
        );

        if ($orderTypeMatch) {
            $fileFormatMatches = [];
            preg_match('/<FileFormat.*>(?<file_format>.*)<\/FileFormat>/', $requestContent, $fileFormatMatches);

            return $this->fixtureOrderType(
                $orderTypeMatches['order_type'],
                ['file_format' => $fileFormatMatches['file_format'] ?? null]
            );
        }

        $transactionPhaseMatches = [];
        $transactionPhaseMatch = preg_match(
            '/<TransactionPhase>(?<transaction_phase>.*)<\/TransactionPhase>/',
            $requestContent,
            $transactionPhaseMatches
        );

        if ($transactionPhaseMatch) {
            return $this->fixtureTransactionPhase($transactionPhaseMatches['transaction_phase']);
        }

        $hevRequestMatch = preg_match(
            '/<ebicsHEVRequest .*>/',
            $requestContent
        );

        if ($hevRequestMatch) {
            return $this->readFixture('hev.xml');
        }

        return new Response();
    }

    /**
     * Fake Order type responses.
     *
     * @param string $orderType
     * @param array|null $options = [
     *     'file_format' => '<string>',
     * ]
     *
     * @return Response
     */
    private function fixtureOrderType(string $orderType, array $options = null): Response
    {
        switch ($orderType) {
            case 'FDL':
                $fileName = sprintf('fdl.%s.xml', $options['file_format']);
                break;
            case 'INI':
            case 'HIA':
            case 'HPB':
            case 'SPR':
            case 'C53':
            case 'STA':
            case 'CCT':
            case 'CDD':
            case 'CDB':
                $fileName = strtolower($orderType).'.xml';
                break;
            default:
                throw new LogicException(sprintf('Faked order type `%s` not supported.', $orderType));
        }

        return $this->readFixture($fileName);
    }

    /**
     * Fake transaction phase responses.
     *
     * @param string $transactionPhase
     *
     * @return Response
     */
    private function fixtureTransactionPhase(string $transactionPhase): Response
    {
        switch ($transactionPhase) {
            case 'Receipt':
            case 'Transfer':
                $fileName = strtolower($transactionPhase).'.xml';
                break;
            default:
                throw new LogicException(sprintf('Faked transaction phase `%s` not supported.', $transactionPhase));
        }

        return $this->readFixture($fileName);
    }

    private function readFixture(string $fileName): Response
    {
        $fixturePath = $this->fixturesDir.'/'.$fileName;

        if (!is_file($fixturePath)) {
            throw new LogicException('Fixtures file does not exists.');
        }

        $response = new Response();

        $responseContent = file_get_contents($fixturePath);

        if (!is_string($responseContent)) {
            throw new LogicException('Response content is not valid.');
        }

        $response->loadXML($responseContent);

        return $response;
    }
}
