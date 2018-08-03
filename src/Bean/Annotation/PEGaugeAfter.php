<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Bean\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ALL")
 */
class PEGaugeAfter
{
    private $namespace = '';
    private $name = '';
    private $help = '';
    private $labels = [];
    private $returnKey = '';

    public function __construct(array $values)
    {
        if (isset($values['namespace']))
        {
            $this->namespace = $values['namespace'];
        }

        if (isset($values['name']))
        {
            $this->name = $values['name'];
        }

        if (isset($values['help']))
        {
            $this->help = $values['help'];
        }
        if (isset($values['labels']))
        {
            $this->labels = $values['labels'];
        }
        if (isset($values['returnKey']))
        {
            $this->returnKey = $values['returnKey'];
        }
    }

    /**
     * @return mixed|string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed|string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed|string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param mixed|string $help
     * @return $this
     */
    public function setHelp($help)
    {
        $this->help = $help;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array|mixed $labels
     * @return $this
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getReturnKey()
    {
        return $this->returnKey;
    }

    /**
     * @param mixed|string $returnKey
     * @return $this
     */
    public function setReturnKey($returnKey)
    {
        $this->returnKey = $returnKey;
        return $this;
    }






}