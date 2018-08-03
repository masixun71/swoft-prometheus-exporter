<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Aop;
use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Swoft\Aop\JoinPoint;
use Swoft\Bean\Annotation\AfterReturning;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\PointAnnotation;
use ExtraSwoft\PrometheusExporter\Bean\Annotation\PECacheTable;
use Swoft\Redis\Redis;


/**
 *
 * @Aspect()
 * @PointAnnotation(include={
 *       PECacheTable::class
 *
 *     })
 */
class PECacheTableAnnotationAspect
{

    /**
     * @Inject()
     * @var PECollectorRegistry
     */
    private $pECollectorRegistry;


    /**
     * @Inject()
     * @var Redis
     */
    private $cache;


    /**
     * @AfterReturning()
     */
    public function AfterReturn(JoinPoint $joinPoint)
    {
        return $joinPoint->getReturn();
    }

}