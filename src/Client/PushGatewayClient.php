<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Client;


use Swoft\Bean\Annotation\Bean;
use Swoft\HttpClient\Client;

/**
 *
 * @Bean()
 * Class PushGateway
 *
 */
class PushGatewayClient
{

    private $client;

    public function __construct()
    {
        $client = new Client([
            'base_uri' => env('PROMETHEUSEXPORTER_PUSHGATEWAY_HOST'),
            'timeout' => 20,
        ]);

        $this->client = $client;
    }

    public function getClient() : Client
    {
        return $this->client;
    }


}