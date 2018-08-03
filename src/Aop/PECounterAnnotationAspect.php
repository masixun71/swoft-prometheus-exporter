<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Aop;
use ExtraSwoft\PrometheusExporter\Bean\Collector\PECounterCollector;
use ExtraSwoft\PrometheusExporter\Collector\PECollectorRegistry;
use Swoft\Aop\ProceedingJoinPoint;
use Swoft\Bean\Annotation\Around;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\PointAnnotation;
use ExtraSwoft\PrometheusExporter\Bean\Annotation\PECounter;


/**
 *
 * @Aspect()
 * @PointAnnotation(include={
 *       PECounter::class
 *
 *     })
 */
class PECounterAnnotationAspect
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

        $peCounterCollector = PECounterCollector::getCollector();

        /** @var PECounter $peCounter */
        $peCounter = $peCounterCollector[$class][$method];

        if ($peCounter->getValue() > 0)
        {
            $this->pECollectorRegistry->counterIncr($peCounter->getNamespace(), $peCounter->getName(), $peCounter->getValue(), $peCounter->getLabels(), $peCounter->getHelp());
        }
        else
        {
            $this->pECollectorRegistry->counterDecr($peCounter->getNamespace(), $peCounter->getName(), (int)abs($peCounter->getValue()), $peCounter->getLabels());
        }


        $result = $proceedingJoinPoint->proceed();

        return $result;
    }

}