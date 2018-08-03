<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Aop;
use ExtraSwoft\PrometheusExporter\Bean\Collector\PEGaugeCollector;
use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Swoft\Aop\ProceedingJoinPoint;
use Swoft\Bean\Annotation\Around;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\PointAnnotation;
use ExtraSwoft\PrometheusExporter\Bean\Annotation\PEGauge;


/**
 *
 * @Aspect()
 * @PointAnnotation(include={
 *       PEGauge::class
 *
 *     })
 *
 */
class PEGaugeAnnotationAspect
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

        $peGaugeCollector = PEGaugeCollector::getCollector();

        /** @var PEGauge $peGauge */
        $peGauge = $peGaugeCollector[$class][$method];

        $this->pECollectorRegistry->gaugeSet($peGauge->getNamespace(), $peGauge->getName(), $peGauge->getValue(), $peGauge->getLabels(), $peGauge->getHelp());

        $result = $proceedingJoinPoint->proceed();

        return $result;
    }

}