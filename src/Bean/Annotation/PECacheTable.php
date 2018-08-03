<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Bean\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ALL")
 */
class PECacheTable
{
    public function __construct(array $values)
    {
    }
}