<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Client;


use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Exception\HttpException;

/**
 *
 * @Bean()
 */
class PushGateway
{

    /**
     * @Inject()
     * @var PushGatewayClient
     */
    private $client;


    public function push(PECollectorRegistry $pECollectorRegistry, $job, $groupingKey)
    {
        $url = '';
        if (!empty($groupingKey)) {
            foreach ($groupingKey as $label => $value) {
                $url .= "/" . $label . "/" . $value;
            }
        }

        $render = $pECollectorRegistry->getRender();

        $result = $this->client->getClient()->put('/metrics/job/' . $job . $url, [
            'headers' => [
                'Content-Type' => 'text/plain; version=0.0.4'
            ],
            'body' => $render
        ]);


        if ($result->getResponse()->getStatusCode() != 202) {
            $msg = "Unexpected status code " . $result->getResponse()->getStatusCode() . " received from pushgateway "  . ": " . $result->getResponse()->getBody()->getContents();
            throw new HttpException($msg);
        }

    }

}