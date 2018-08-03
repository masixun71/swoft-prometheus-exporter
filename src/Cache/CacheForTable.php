<?php
declare(strict_types=1);

namespace ExtraSwoft\PrometheusExporter\Cache;


use ExtraSwoft\PrometheusExporter\Boot\PrometheusExporterTable;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
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
        $lock = $this->prometheusExporterTable->getLock();

        $res = $lock->trylock();
        if ($res)
        {
            $this->getCounterTable();
            $this->getGaugeTable();
            $this->getHistogramTable();

            $this->init = true;
            $lock->unlock();
        }
        else
        {
            $this->init = true;
        }
    }



    private function getCounterTable()
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $resultCounter = $this->cache->sMembers($redisPrefix . 'counterTable');

        if ($resultCounter)
        {
            $this->counterMembers = $resultCounter;
            $counterTable = $this->prometheusExporterTable->getCounterTable();
            $this->setTable($resultCounter, 'counterTableValue:', $counterTable);
        }

    }

    private function getGaugeTable()
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $resultGauge = $this->cache->sMembers($redisPrefix . 'gaugeTable');

        if ($resultGauge)
        {
            $this->gaugeMembers = $resultGauge;
            $gaugeTable = $this->prometheusExporterTable->getGaugeTable();
            $this->setTable($resultGauge, 'gaugeTableValue:', $gaugeTable);
        }
    }

    private function getHistogramTable()
    {
        $redisPrefix = env('PROMETHEUSEXPORTER_REDIS_PREFIX');
        $resultHistogram = $this->cache->sMembers($redisPrefix . 'histogramTable');

        if ($resultHistogram)
        {
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