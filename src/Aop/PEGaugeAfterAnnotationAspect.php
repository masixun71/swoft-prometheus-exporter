<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Aop;
use ExtraSwoft\PrometheusExporter\Bean\Collector\PEGaugeAfterCollector;
use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Swoft\Aop\ProceedingJoinPoint;
use Swoft\Bean\Annotation\Around;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\PointAnnotation;
use ExtraSwoft\PrometheusExporter\Bean\Annotation\PEGaugeAfter;


/**
 *
 * @Aspect()
 * @PointAnnotation(include={
 *       PEGaugeAfter::class
 *
 *     })
 *
 */
class PEGaugeAfterAnnotationAspect
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

        $peGaugeAfterCollector = PEGaugeAfterCollector::getCollector();

        $result = $proceedingJoinPoint->proceed();

        /** @var PEGaugeAfter $peGaugeAfter */
        $peGaugeAfter = $peGaugeAfterCollector[$class][$method];


        $arrKey = explode(",", $peGaugeAfter->getReturnKey());
        $arrResult = $result;
        $value = '';
        foreach ($arrKey as $key)
        {
            $value = $arrResult[$key];
            $arrResult = $value;
        }


        $this->pECollectorRegistry->gaugeSet($peGaugeAfter->getNamespace(), $peGaugeAfter->getName(), $value, $peGaugeAfter->getLabels(), $peGaugeAfter->getHelp());


        return $result;
    }




}