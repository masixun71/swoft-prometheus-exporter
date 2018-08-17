<?php


namespace ExtraSwoft\PrometheusExporter\Collector;


use ExtraSwoft\PrometheusExporter\Boot\PrometheusExporterTable;
use ExtraSwoft\PrometheusExporter\Metric\Histogram;
use ExtraSwoft\PrometheusExporter\Exception\MetricsRegistrationException;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Memory\Table;
use Swoft\Redis\Redis;


/**
 *
 * @Bean()
 * Class CollectorRegistry
 * @package App\PrometheusExporter\Collector
 */
class PECollectorRegistry
{

    const RE_METRIC_LABEL_NAME = '/^[a-zA-Z_:][a-zA-Z0-9_:]*$/';

    /**
     */
    private $gauges = array();
    /**
     */
    private $counters = array();
    /**
     */
    private $histograms = array();


    /**
     * @Inject()
     * @var PrometheusExporterTable
     */
    private $prometheusExporterTable;


    /**
     * @Inject()
     * @var Redis
     */
    private $cache;


    /**
     * @param string $namespace
     * @param string $name
     * @param array $labels
     * @param $value
     * @param string $help
     *
     * 不增加get方法 ，数据源只为PE提供
     *
     */
    public function gaugeSet(string $namespace, string $name, $value, array $labels = [], string $help = '')
    {
        $metricName = $this->getMetricName($namespace, $name);
        $labelString = $this->getLabelString($labels);

        $struct = [
            'metricName'   => $metricName,
            'labelString'  => $labelString,
            'value' => $value,
            'updateTime'  => (time() * 1000) . "",
            'help' => $help,
        ];

        $cacheKey = md5($metricName . $labelString);
        $this->prometheusExporterTable->getGaugeTable()->set($cacheKey, $struct);


        $this->gauges[$cacheKey] = 1;

    }

    /**
     * @param string $namespace
     * @param string $name
     * @param array $labels
     * @param int $value
     * @param string $help
     *
     * 不增加get方法 ，数据源只为PE提供
     *
     */
    public function counterIncr(string $namespace, string $name, int $value, array $labels = [], string $help = '')
    {
        $metricName = $this->getMetricName($namespace, $name);
        $labelString = $this->getLabelString($labels);

        $cacheKey = md5($metricName . $labelString);


        $this->incrTable($this->prometheusExporterTable->getCounterTable(), $cacheKey, $value, $metricName, $labelString, $help);

        $this->counters[$cacheKey] = 1;
    }

    /**
     * @param string $namespace
     * @param string $name
     * @param array $labels
     * @param int $value
     * @param string $help
     *
     * 不增加get方法 ，数据源只为PE提供
     *
     */
    public function counterDecr(string $namespace, string $name, int $value, array $labels = [])
    {
        $metricName = $this->getMetricName($namespace, $name);
        $labelString = $this->getLabelString($labels);

        $cacheKey = md5($metricName . $labelString);

        if ($this->prometheusExporterTable->getCounterTable()->exist($cacheKey))
        {
            $this->prometheusExporterTable->getCounterTable()->decr($cacheKey, 'value', $value);
        }
        else
        {
            throw new MetricsRegistrationException("no key to incr, why decr?");
        }

        $this->counters[$cacheKey] = 1;
    }

    public function histogramIncr(string $namespace, string $name, float $value, array $labels = [], array $defaultBuckets = [], string $help = '')
    {
        $histogram = new Histogram($namespace, $name, $labels, $defaultBuckets, $help);
        $histogram->incr($value);
        $metricName = $this->getMetricName($histogram->getNamespace(), $histogram->getName());
        $bucketMetricName = $metricName . '_bucket';
        $items = $histogram->getItems();
        foreach ($items as $item)
        {
            $labelString = $this->getLabelString($item['label']);
            $cacheKey = md5($bucketMetricName . $labelString);
            $this->incrTable($this->prometheusExporterTable->getHistogramTable(), $cacheKey, $item['value'], $bucketMetricName, $labelString, $histogram->getHelp());

            $this->histograms[$cacheKey] = 1;
        }

        $labelString = $this->getLabelString($histogram->getLabels());
        $sumMetricName = $metricName . '_sum';
        $sumCacheKey = md5($sumMetricName . $labelString);
        $this->incrTable($this->prometheusExporterTable->getHistogramTable(), $sumCacheKey, $histogram->getSum(), $sumMetricName, $labelString, $histogram->getHelp());
        $this->histograms[$sumCacheKey] = 1;

        $countMetricName = $metricName . '_count';
        $countCacheKey = md5($countMetricName . $labelString);
        $this->incrTable($this->prometheusExporterTable->getHistogramTable(), $countCacheKey, $histogram->getCount(), $countMetricName, $labelString, $histogram->getHelp());
        $this->histograms[$countCacheKey] = 1;

    }


    private function getMetricName($namespace, $name)
    {
        $metricName = ($namespace ? $namespace . '_' : '') . $name;
        if (!preg_match(self::RE_METRIC_LABEL_NAME, $metricName)) {
            throw new \InvalidArgumentException("Invalid metric name: '" . $metricName . "'");
        }

        return $metricName;
    }

    private function getLabelString($labels)
    {
        if (empty($labels))
        {
            return ' ';
        }

        $escapedLabels = [];
        foreach ($labels as $labelName => $labelValue) {
            $escapedLabels[] = $labelName . '="' . $this->escapeLabelValue($labelValue) . '"';
        }

        return '{' . implode(',', $escapedLabels) . '} ';
    }

    private function escapeLabelValue($v)
    {
        $v = str_replace("\\", "\\\\", $v);
        $v = str_replace("\n", "\\n", $v);
        $v = str_replace("\"", "\\\"", $v);
        return $v;
    }

    private function incrTable(Table $table, $cacheKey, $value, $metricName, $labelString, $help)
    {
        if ($table->exist($cacheKey))
        {
            $table->incr($cacheKey, 'value', $value);
        }
        else
        {
            $struct = [
                'metricName'   => $metricName,
                'labelString'  => $labelString,
                'value' => $value,
                'updateTime'  => (time() * 1000) . "",
                'help' => $help,
            ];
            $table->set($cacheKey, $struct);
        }
    }

    /**
     * @return mixed
     */
    public function getGauges()
    {
        return $this->gauges;
    }

    /**
     * @return mixed
     */
    public function getCounters()
    {
        return $this->counters;
    }

    /**
     * @return mixed
     */
    public function getHistograms()
    {
        return $this->histograms;
    }

    /**
     * @return PrometheusExporterTable
     */
    public function getPrometheusExporterTable(): PrometheusExporterTable
    {
        return $this->prometheusExporterTable;
    }


    public function getSingleRender($origin, Table $table, $type, $addTimestamp = false)
    {
        if (empty($origin))
        {
            return '';
        }

        $lines = array();
        $mapMetricName = [];

        foreach ($origin as $key => $value)
        {
            $metric = $table->get($key);
            if (!isset($mapMetricName[$metric['metricName']]))
            {
                $lines[] = "# HELP " . $metric['metricName'] . " {$metric['help']}";
                $lines[] = "# TYPE " . $metric['metricName'] . ' ' . $type;
                $mapMetricName[$metric['metricName']] = 1;
            }
            if ($addTimestamp)
            {
                $time = ' ' . $metric['updateTime'];
            }
            else
            {
                $time = '';
            }
            $lines[] = $metric['metricName'].$metric['labelString'].$metric['value']. $time;
        }
        return implode("\n", $lines) . "\n";
    }

    public function getHistogramRender($addTimestamp = false)
    {
        if (empty($this->getHistograms()))
        {
            return '';
        }


        $lines = array();
        $map = [];

        foreach ($this->getHistograms() as $key => $value)
        {
            $metric = $this->getPrometheusExporterTable()->getHistogramTable()->get($key);

            if (!isset($map[$metric['metricName']]))
            {
                if (strpos($metric['metricName'],'sum') > 0 || strpos($metric['metricName'],'count') > 0)
                {
                }
                else
                {
                    $metricName = str_replace('_bucket', '', $metric['metricName']);
                    $lines[] = "\n# HELP " . $metricName . " {$metric['help']}";
                    $lines[] = "# TYPE " . $metricName . " histogram";
                    $map[$metric['metricName']] = 1;
                }
            }

            if ($addTimestamp)
            {
                $time = ' ' . $metric['updateTime'];
            }
            else
            {
                $time = '';
            }

            $lines[] = $metric['metricName'].$metric['labelString'].(string)$metric['value'] . $time;
        }
        return implode("\n", $lines) . "\n";
    }

    public function getRender()
    {
        return $this->getSingleRender($this->getCounters(), $this->getPrometheusExporterTable()->getCounterTable(), 'counter') . "\n" .
            $this->getSingleRender($this->getGauges(), $this->getPrometheusExporterTable()->getGaugeTable(), 'gauge') . "\n" .
            $this->getHistogramRender();
    }


    public function cacheTable($job, $instance)
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        if (!empty($this->getCounters()))
        {
            $this->addCache($redisPrefix . $job . $instance, $this->getCounters(), 'counterTable', 'counterTableValue:', $this->prometheusExporterTable->getCounterTable());
        }
        if (!empty($this->getGauges()))
        {
            $this->addCache($redisPrefix . $job . $instance, $this->getGauges(), 'gaugeTable', 'gaugeTableValue:', $this->prometheusExporterTable->getGaugeTable());
        }
        if (!empty($this->getHistograms()))
        {
            $this->addCache($redisPrefix . $job . $instance, $this->getHistograms(), 'histogramTable', 'histogramTableValue:', $this->prometheusExporterTable->getHistogramTable());
        }

    }

    private function addCache($redisPrefix, $map, $mapKey, $mapValueKey, Table $mapTable)
    {
        $this->cache->sAdd($redisPrefix . $mapKey, ...array_keys($map));

        foreach ($map as $key => $value)
        {
            $this->cache->set($redisPrefix . $mapValueKey . $key, \swoole_serialize::pack($mapTable->get($key)));
        }
    }
}
