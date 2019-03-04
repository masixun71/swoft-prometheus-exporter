<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Cache;


use ExtraSwoft\PrometheusExporter\Boot\PrometheusExporterTable;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Exception\Exception;
use Swoft\Redis\Redis;

/**
 * @Bean()
 */
class CacheForTable
{

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


    private $counterMembers = [];
    private $gaugeMembers = [];
    private $histogramMembers = [];


    private $init = false;


    public function getValueFromCache()
    {
        $counterTable = $this->prometheusExporterTable->getCounterTable();

        if (!$counterTable->exist('setTable'))
        {
            if (env('PROMETHEUSEXPORTER_PERSISTENCE', false)) {
                $this->getCounterTable();
                $this->getGaugeTable();
                $this->getHistogramTable();
            }

            $counterTable->set('setTable', [
                'metricName'   => 'setTable',
                'labelString'  => '',
                'value' => 1,
                'updateTime'  => '',
                'help' => '',
            ]);
            $this->init = true;
        }
        else
        {
            $this->init = true;
        }
    }



    private function getCounterTable()
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $line = env('PROMETHEUSEXPORTER_COUNTER_LINE');
        $resultCounter = $this->cache->sMembers($redisPrefix . 'counterTable');
        if ($resultCounter)
        {
            if ((int)(count($resultCounter) * 1.2) > $line)
            {
                throw new Exception("counterTable 缓存数据可能超过你设置的行数，请检查并扩大");
            }


            $this->counterMembers = $resultCounter;
            $counterTable = $this->prometheusExporterTable->getCounterTable();
            $this->setTable($resultCounter, 'counterTableValue:', $counterTable);
        }

    }

    private function getGaugeTable()
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $line = env('PROMETHEUSEXPORTER_GAUGE_LINE');
        $resultGauge = $this->cache->sMembers($redisPrefix . 'gaugeTable');

        if ($resultGauge)
        {
            if ((int)(count($resultGauge) * 1.2) > $line)
            {
                throw new Exception("gaugeTable 缓存数据可能超过你设置的行数，请检查并扩大");
            }


            $this->gaugeMembers = $resultGauge;
            $gaugeTable = $this->prometheusExporterTable->getGaugeTable();
            $this->setTable($resultGauge, 'gaugeTableValue:', $gaugeTable);
        }
    }

    private function getHistogramTable()
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $line = env('PROMETHEUSEXPORTER_HISTOGRAM_LINE');
        $resultHistogram = $this->cache->sMembers($redisPrefix . 'histogramTable');
        if ($resultHistogram)
        {
            if ((int)(count($resultHistogram) * 1.2) > $line)
            {
                throw new Exception("histogramTable 缓存数据可能超过你设置的行数，请检查并扩大");
            }
            $this->histogramMembers = $resultHistogram;
            $histogramTable = $this->prometheusExporterTable->getHistogramTable();
            $this->setTable($resultHistogram, 'histogramTableValue:', $histogramTable);
        }
    }


    private function setTable($result, $mapKey, $table)
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $count = count($result);
        for ($i = 0; $i < $count; $i+=30)
        {
            $sliceResult = array_slice($result, $i, 30);
            $realCacheKeys = [];
            foreach ($sliceResult as $value)
            {
                $realCacheKeys[] = $redisPrefix . $mapKey . $value;
            }

            $resultValue = $this->cache->mget($realCacheKeys);

            foreach ($resultValue as $key => $value)
            {
                $resKey = explode(":", $key);
                $tableKey = $resKey[count($resKey) - 1];

                if (is_string($value))
                {
                    $arr = \swoole_serialize::unpack($value);
                    if (is_array($arr))
                    {
                        $table->set($tableKey, $arr);
                    }

                }
            }
        }
    }




    public function getInit(): bool
    {
        return $this->init;
    }

}