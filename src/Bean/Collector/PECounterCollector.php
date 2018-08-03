<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Bean\Collector;


use Swoft\Bean\CollectorInterface;

class PECounterCollector implements CollectorInterface
{

    private static $pECounter = [];


    /**
     * @param string $className
     * @param object|null $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param string|null $propertyValue
     * @return mixed
     */
    public static function collect(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    )
    {
        self::$pECounter[$className][$methodName] = $objectAnnotation;
    }

    /**
     * @return mixed
     */
    public static function getCollector()
    {
        return self::$pECounter;
    }
}