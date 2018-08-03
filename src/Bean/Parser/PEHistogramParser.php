<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Bean\Parser;


use ExtraSwoft\PrometheusExporter\Bean\Collector\PEHistogramCollector;
use Swoft\Bean\Collector;
use Swoft\Bean\Parser\AbstractParser;


class PEHistogramParser extends AbstractParser
{

    /**
     * 解析注解
     *
     * @param string $className
     * @param object $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param string|null $propertyValue
     *
     * @return mixed
     */
    public function parser(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    )
    {
        Collector::$methodAnnotations[$className][$methodName][] = get_class((object)$objectAnnotation);
        PEHistogramCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);
    }
}