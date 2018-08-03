<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Bean\Wrapper;


use ExtraSwoft\PrometheusExporter\Bean\Annotation\PEGaugeAfter;
use Swoft\Bean\Wrapper\AbstractWrapper;

class PEGaugeAfterWrapper extends AbstractWrapper
{

    /**
     * 类注解
     *
     * @var array
     */
    protected $classAnnotations = [];

    /**
     * 属性注解
     *
     * @var array
     */
    protected $propertyAnnotations = [];

    /**
     * 方法注解
     *
     * @var array
     */
    protected $methodAnnotations = [
        PEGaugeAfter::class
    ];



    /**
     * 是否解析类注解
     *
     * @param array $annotations
     * @return bool
     */
    public function isParseClassAnnotations(array $annotations): bool
    {
        return false;
    }

    /**
     * 是否解析属性注解
     *
     * @param array $annotations
     * @return bool
     */
    public function isParsePropertyAnnotations(array $annotations): bool
    {
        return false;
    }

    /**
     * 是否解析方法注解
     *
     * @param array $annotations
     * @return bool
     */
    public function isParseMethodAnnotations(array $annotations): bool
    {
        return true;
    }
}