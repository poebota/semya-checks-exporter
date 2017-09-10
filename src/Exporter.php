<?php

namespace SemyaChecksExporter;

use Buzz\Message\Response;
use SemyaChecksExporter\Data;
use Buzz\Client\Curl;
use Buzz\Message\Request;

class Exporter
{
    const HOST = 'http://api.semya.vigroup.ru';

    const LOGIN_WITH_CARD_URL = '/api4/userInfo/loginWithCard';
    const GET_INFO_URL = '/api3/purchasesInfo/getInfo';
    const GET_DETAILS_INFO_URL = '/api3/purchasesInfo/getDetailsInfo';
    const DELETE_DEVICE_TOKEN_URL = '/api3/userInfo/deleteDeviceToken';

    const PAGE_SIZE = 100;

    const REQUEST_HEADERS = [
        'X-Requested-With' => 'XMLHttpRequest',
        'Content-Type'     => 'application/x-www-form-urlencoded',
        'User-Agent'       => 'Mozilla/5.0 (Linux; Android 7.0;SAMSUNG SM-G955F Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/5.2 Chrome/51.0.2704.106 Mobile Safari/537.36',
    ];

    /**
     * @var Data\Config
     */
    private $config;

    public function __construct(Data\Config $config)
    {
        $this->client = new Curl();
        $this->config = $config;
    }

    public function export(\DateTime $startDate, \DateTime $endDate) : \Generator
    {
        $this->tryLogin($this->config->udid);
        $sinceLastId = 0;

        do {
            $request = $this->createRequest(
                self::GET_INFO_URL,
                $data = Data\Info::createFromConfig($this->config)
                    ->setCount(self::PAGE_SIZE)
                    ->setSinceLastId($sinceLastId)
                    ->setFromDate($startDate->getTimestamp())
                    ->setToDate($endDate->getTimestamp())
            );
            $this->client->send($request, $response = new Response());

            $jsonResponse = @json_decode($response->getContent(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('Invalid json response: ' . json_last_error_msg() . "\n$request\n{$response}\n");
            }

            if (
                !isset($jsonResponse['checks']) ||
                !is_array($jsonResponse['checks'])
            ) {
                throw new \RuntimeException('Invalid json response: no checks found');
            }

            foreach ($jsonResponse['checks'] as $check) {
                $detailedCheck = $check;

                if (!isset($check['purchaseId'])) {
                    throw new \RuntimeException("'purchaseId' is missing");
                }

                $sinceLastId = $check['purchaseId'];

                yield array_merge(
                    $detailedCheck,
                    ['detailsInfo' => $this->loadInfo($check['purchaseId'])]
                );
            }

        } while (count($jsonResponse['checks']) > 0);
    }

    protected function loadInfo(string $purchaseId) : array
    {
        $request = $this->createRequest(
            self::GET_DETAILS_INFO_URL,
            Data\DetailsInfo::createFromConfig($this->config)
                ->setPurchaseId($purchaseId)
        );
        $this->client->send($request, $response = new Response());

        $jsonResponse = @json_decode($response->getContent(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('Invalid json response: ' . json_last_error_msg() . "\n$request\n{$response}\n");
        }

        if (!is_array($jsonResponse)) {
            throw new \RuntimeException('Invalid json response: no checks found');
        }

        return $jsonResponse;
    }

    protected function tryLogin(string $udid)
    {
        $this->config->token = $this->generateToken(
            $this->config->cardId,
            $udid
        );

        $request = $this->createRequest(
            self::LOGIN_WITH_CARD_URL,
            Data\Login::createFromConfig($this->config)
                ->setUdid($udid)
        );
        $this->client->send($request,$response = new Response());

        $jsonResponse = @json_decode($response->getContent(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('Invalid json response: ' . json_last_error_msg() . "\n$request\n{$response}\n");
        }

        if (!isset(
            $jsonResponse['deviceId'],
            $jsonResponse['cardId']
        )) {
            throw new \RuntimeException('Unable to login'. "\n" . urldecode($request) . "\n{$response}\n");
        }

        $this->config->deviceId = $jsonResponse['deviceId'];
        $this->config->canonicalCardId = $jsonResponse['cardId'];
        $this->config->token = $this->generateToken($this->config->canonicalCardId, $this->config->deviceId);
    }

    public function unregister(string $udid)
    {
        $this->tryLogin($udid);
        $request = $this->createRequest(self::DELETE_DEVICE_TOKEN_URL, Data\Unregister::createFromConfig($this->config));
        $this->client->send($request, $response = new Response());

        $jsonResponse = @json_decode($response->getContent(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('Invalid json response: ' . json_last_error_msg() . "\n$request\n{$response}\n");
        }

        if (
            !isset($jsonResponse['success']) ||
            (1 != $jsonResponse['success'])
        ) {
            throw new \RuntimeException('Unable to unregister device ' . "\n$request\n{$response}\n");
        }
    }

    public function register()
    {
        $this->tryLogin($udid = bin2hex(random_bytes(8)));

        return $udid;
    }

    protected function sha256Pad(string $input) : string
    {
        $result = '';
        $digest = str_split(hash('sha256', $input));

        foreach (array_chunk($digest, 2) as $byteString) {
            $byteString = implode($byteString);
            $hex        = dechex(hexdec($byteString) & 255);

            if (strlen($hex) === 1) {
                $result .= '0';
            }

            $result .= $hex;
        }

        return strtoupper($result);
    }

    protected function md5Pad(string $input) : string
    {
        $result = '';
        $digest = str_split(hash('md5', $input));

        foreach (array_chunk($digest, 2) as $byteString) {
            $byteString = implode($byteString);
            $hex        = dechex(hexdec($byteString) & 255);

            if (strlen($hex) < 2) {
                $hex = '0' . $hex;
            }

            $result .= $hex;
        }

        return strtoupper($result);
    }

    protected function generateToken(string $prefix, string $postfix) : string
    {
        return $this->sha256Pad($prefix . $this->md5Pad($this->config->secret) . $postfix);
    }

    /**
     * @param string $endpoint
     * @param mixed $data
     *
     * @return Request
     */
    protected function createRequest(string $endpoint, $data) : Request
    {
        $request = new Request('POST', $endpoint, self::HOST);
        $request->setHeaders(self::REQUEST_HEADERS);
        $request->setContent(http_build_query(['data' => json_encode($data, JSON_UNESCAPED_UNICODE)]));

        return $request;
    }
}