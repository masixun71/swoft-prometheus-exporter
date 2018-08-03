<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Aop;
use ExtraSwoft\PrometheusExporter\Bean\Collector\PEHistogramCollector;
use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Swoft\Aop\JoinPoint;
use Swoft\Aop\ProceedingJoinPoint;
use Swoft\Bean\Annotation\AfterReturning;
use Swoft\Bean\Annotation\Around;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\PointAnnotation;
use ExtraSwoft\PrometheusExporter\Bean\Annotation\PEHistogram;


/**
 *
 * @Aspect()
 * @PointAnnotation(include={
 *       PEHistogram::class
 *
 *     })
 */
class PEHistogramAnnotationAspect
{

    /**
     * @Inject()
     * @var PECollectorRegistry
     */
    private $pECollectorRegistry;

    /**
     * @Around()
     */
    public function around(ProceedingJoinPoint $proceedingJoinPoint)
    {

        $class = get_class($proceedingJoinPoint->getTarget());
        $method = $proceedingJoinPoint->getMethod();

        $peGaugeCollector = PEHistogramCollector::getCollector();
        /** @var PEHistogram $peHistogram */
        $peHistogram = $peGaugeCollector[$class][$method];

        $startMicroTime = (microtime(true) * 1000);
        $result = $proceedingJoinPoint->proceed();
        $endMicroTime = (microtime(true) * 1000);
        $value = ($endMicroTime - $startMicroTime) / 1000;

        $this->pECollectorRegistry->histogramIncr($peHistogram->getNamespace(), $peHistogram->getName(), $value, $peHistogram->getLabels(), $peHistogram->getDefaultBuckets(), $peHistogram->getHelp());


        return $result;
    }

}