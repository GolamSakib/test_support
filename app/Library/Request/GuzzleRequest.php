<?php

namespace App\Library\Request;

use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class GuzzleRequest
{
    /**
     * @var string
     */
    private $base_url = 'http://203.188.254.204/satori/api/';

    /**
     * url pattern
     * http://risesrv.rise-brand.com/RISE/api/app/GetCustomerByMobile/01719679021
     *   http://risesrv.rise-brand.com/RISE/api/app/GetCustomer/01001052
     */

    /**
     * @var string
     */
    private $apiKey = 'joy:9S6YSR0+KnAU8yl2cqJCMQ==';

    /**
     * @var
     */
    private $header;

    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var
     */
    private $response;

    /**
     * @var bool
     */
    private $verify = false;

    /**
     * @param $apiUrl
     * @param array $body
     * @param string $orderId
     * @return void
     * @throws Exception
     */
    public function post($apiUrl, $body, $orderId='')
    {

        try {
            $client = $this->setupClient();
            $response = $client->post($apiUrl, $body);


            if ($response->getStatusCode() == 200) {
                $responseBody = $response->getBody();
                DB::table('orders')
                    ->where('orders_id', $orderId)
                    ->where('is_sync', '!=', 1)
                    ->update(['is_sync' => 1, 'updated_at' => Carbon::now()]);


                return json_decode($responseBody->getContents());

            } else {
                return 'Request Failed';
            }

        } catch (ClientException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $apiUrl
     * @param array $queryString
     * @return void
     * @throws Exception
     */
    public function get($apiUrl, array $queryString = [])
    {

        try {
            $client = $this->setupClient();

            $response = $client->get($apiUrl, $queryString);
            if ($response->getStatusCode() == 200) {
                $responseBody = $response->getBody();

                return json_decode($responseBody->getContents());

            } else {
                return 'Request Failed';
            }

        } catch (ClientException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function setBaseUrl()
    {
        $cloudPosBase = DB::table('settings')
            ->where('name', '=', 'cloudpos_base_url')
            ->first();

        if ($cloudPosBase != null) {
            $this->base_url = $cloudPosBase->value;
        } else {
            throw new Exception('cloudpos_base_url does not exist in settings table');
        }
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function setApiKey()
    {
        $cloudPosApiKey = DB::table('settings')
            ->where('name', '=', 'cloudpos_api_key')
            ->first();
        if ($cloudPosApiKey != null) {
            $this->apiKey = $cloudPosApiKey->value;
        } else {
            throw new Exception('cloudpos_api_key does not exist in settings table');
        }
    }

    /**
     * @return array
     */
    private function getHeader(): array
    {
        return [
            'Authorization' => $this->getApiKey(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @return Client
     */
    private function setupClient(): Client
    {
        return new Client([
            'base_uri' => $this->getBaseUrl(),
            'verify' => $this->verify,
            'headers' => $this->getHeader()
        ]);
    }
}
