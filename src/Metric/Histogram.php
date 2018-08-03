<?php

namespace ExtraSwoft\PrometheusExporter\Metric;



class Histogram
{

    private $defaultBuckets;

    private $namespace;
    private $name;
    private $labels;
    private $help;
    private $sum;
    private $count;
    private $buckets;


    public function __construct(string $namespace, string $name, array $labels = [], array $defaultBuckets = [], string $help = '')
    {
        if (empty($defaultBuckets))
        {
            $this->defaultBuckets = $this->getDefaultBuckets();
        }
        else
        {
            $this->defaultBuckets = $defaultBuckets;
        }

        foreach ($labels as $label) {
            if ($label === 'le') {
                throw new \InvalidArgumentException("Histogram cannot have a label named 'le'.");
            }
        }

        $this->namespace = $namespace;
        $this->name = $name;
        $this->labels = $labels;
        $this->help = $help;

        foreach ($this->defaultBuckets as $bucket)
        {
            $this->buckets[(string)$bucket] = 0;
        }

        $this->buckets['+Inf'] = 0;
        $this->sum = 0.0;
        $this->count = 0;
    }


    /**
     * List of default buckets suitable for typical web application latency metrics
     * @return array
     */
    private function getDefaultBuckets()
    {
        return array(
            0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0
        );
    }


    public function incr(float $value)
    {
        foreach ($this->defaultBuckets as $bucket)
        {
            if ($value <= $bucket)
            {
                $this->buckets[(string)$bucket]++;
            }
        }
        $this->buckets['+Inf']++;
        $this->count++;
        $this->sum = (float)$this->sum + (float)$value;
    }

    public function getItems()
    {
        $items = [];
        foreach ($this->buckets as $key => $value)
        {
            $labels = array_merge($this->labels, ['le' => $key]);

            $items[] = [
                'label' => $labels,
                'value' => $value
            ];
        }

        return $items;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }



}
