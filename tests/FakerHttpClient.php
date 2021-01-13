<?php

namespace AndrewSvirin\Ebics\Tests;

use AndrewSvirin\Ebics\Contracts\HttpClientInterface;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use LogicException;

/**
 * Class EbicsClientTest.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @group ebics-client
 */
class FakerHttpClient implements HttpClientInterface
{

    /**
     * @var string
     */
    private $fixturesDir;

    public function __construct(string $fixturesDir)
    {
        $this->fixturesDir = $fixturesDir;
    }

    public function post(
        string $url,
        Request $request
    ): Response {
        $requestContent = $request->getContent();
        if (preg_match('/<OrderType>(?<order_type>.*)<\/OrderType>/', $requestContent, $matches) && !empty($matches)) {
            return $this->fixtureOrderType($matches['order_type']);
        } elseif (preg_match('/<TransactionPhase>(?<transaction_phase>.*)<\/TransactionPhase>/', $requestContent,
                $matches) || empty($matches)) {
            return $this->fixtureTransactionPhase($matches['transaction_phase']);
        } else {
            return new Response();
        }
    }

    /**
     * Fake Order type responses.
     *
     * @param string $orderType
     *
     * @return Response
     */
    private function fixtureOrderType(string $orderType): Response
    {
        switch ($orderType) {
            case 'FDL':
                $fileName = 'fdl.xml';
                break;
            default:
                throw new LogicException(sprintf('Faked order type `%s` not supported.', $orderType));
        }

        $fixturePath = $this->fixturesDir . '/' . $fileName;

        if (!is_file($fixturePath)) {
            throw new LogicException('Fixtures file doe not exists.');
        }

        $response = new Response();

        $responseContent = file_get_contents($fixturePath);

        $response->loadXML($responseContent);

        return $response;
    }

    /**
     * Fake transaction phase responses.
     *
     * @param $transactionPhase
     *
     * @return Response
     */
    private function fixtureTransactionPhase($transactionPhase): Response
    {
        switch ($transactionPhase) {
            case 'Receipt':
                $fileName = 'receipt.xml';
                break;
            default:
                throw new LogicException(sprintf('Faked transaction phase `%s` not supported.', $transactionPhase));
        }

        $fixturePath = $this->fixturesDir . '/' . $fileName;

        if (!is_file($fixturePath)) {
            throw new LogicException('Fixtures file doe not exists.');
        }

        $response = new Response();

        $responseContent = file_get_contents($fixturePath);

        $response->loadXML($responseContent);

        return $response;
    }
}
