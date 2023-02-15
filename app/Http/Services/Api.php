<?php

namespace App\Http\Services;

use GuzzleHttp\Client as Client;

class Api
{
    protected $client; 

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function get($url, $param = array())
    {
        $response = $this->client->request('GET', $url, ['query' => $param]);
        $statusCode = $response->getStatusCode();
        $result = $response->getBody()->getContents();
        return $this->toArray($result);
    }

    protected function toArray($json)
    {
        return json_decode($json,1);
    }
}
