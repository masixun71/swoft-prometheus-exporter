<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Middlewares;

use ExtraSwoft\PrometheusExporter\Bean\Collector\PECacheTableCollector;
use ExtraSwoft\PrometheusExporter\Cache\CacheForTable;
use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Core\RequestContext;
use Swoft\Http\Message\Middleware\MiddlewareInterface;


/**
 * @Bean()
 */
class InitPrometheusExporterMiddleware implements MiddlewareInterface
{

    /**
     * @Inject()
     * @var CacheForTable
     */
    private $cacheForTable;

    /**
     * @Inject()
     * @var PECollectorRegistry
     */
    private $collectorRegistry;



    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->cacheForTable->getInit())
        {
            $this->cacheForTable->getValueFromCache();
        }

        $uri = $request->getSwooleRequest()->server['request_uri'];
        $uri = ltrim($uri, '/');
        $uri = str_replace('/', '_', $uri);
        $startMicroTime = (microtime(true) * 1000);
        $response = $handler->handle($request);
        $endMicroTime = (microtime(true) * 1000);
        $value = ($endMicroTime - $startMicroTime) / 1000;
        if (!empty($uri))
        {
            $this->collectorRegistry->counterIncr('http_request', 'total', 1, ['source' => 'all']);
            $this->collectorRegistry->counterIncr('http_request', 'total', 1, ['source' => 'api', 'uri' => $uri, 'code' => (string)$response->getStatusCode()]);
            $this->collectorRegistry->histogramIncr('http_request', 'duration_seconds', $value, ['source' => 'all']);
            $this->collectorRegistry->histogramIncr('http_request', 'duration_seconds', $value, ['source' => 'api', 'uri' => $uri]);

            if (isset(PECacheTableCollector::getCollector()[RequestContext::getContextData()['controllerClass']][RequestContext::getContextData()['controllerAction']]))
            {
                $cacheTable = PECacheTableCollector::getCollector()[RequestContext::getContextData()['controllerClass']][RequestContext::getContextData()['controllerAction']];
                if ($cacheTable)
                {
                    $this->collectorRegistry->cacheTable();
                }
            }
        }

        return $response;
    }

}