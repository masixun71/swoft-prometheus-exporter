<?php
/**
 * This file is part of Swoft.
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace ExtraSwoft\PrometheusExporter\Boot;
use Swoft\Bean\Annotation\BootBean;
use Swoft\Bootstrap\Boots\LoadEnv;
use Swoft\Memory\Table;


/**
 * Custom process
 *
 * @BootBean()
 */
class PrometheusExporterTable
{

    private $counterTable;

    private $gaugeTable;

    private $histogramTable;


    public function __construct()
    {
        (new LoadEnv())->bootstrap();

        $counterStruct = [
            'metricName'   => [Table::TYPE_STRING, 100],
            'labelString'  => [Table::TYPE_STRING, 255],
            'value' => [Table::TYPE_INT, Table::EIGHT_INT_LENGTH],
            'updateTime'  => [Table::TYPE_STRING, 15],
            'help' => [Table::TYPE_STRING, 255],
        ];
        $this->counterTable = new Table('counterPETable', env('PROMETHEUSEXPORTER_COUNTER_LINE'), $counterStruct);
        $this->counterTable->create();

        $gaugeStruct = [
            'metricName'   => [Table::TYPE_STRING, 100],
            'labelString'  => [Table::TYPE_STRING, 255],
            'value' => [Table::TYPE_STRING, 255],
            'updateTime'  => [Table::TYPE_STRING, 15],
            'help' => [Table::TYPE_STRING, 255],
        ];
        $this->gaugeTable = new Table('gaugePETable', env('PROMETHEUSEXPORTER_GAUGE_LINE'), $gaugeStruct);
        $this->gaugeTable->create();

        $histogramStruct = [
            'metricName'   => [Table::TYPE_STRING, 100],
            'labelString'  => [Table::TYPE_STRING, 255],
            'value' => [Table::TYPE_FLOAT, 8],
            'updateTime'  => [Table::TYPE_STRING, 15],
            'help' => [Table::TYPE_STRING, 255],
        ];
        $this->histogramTable = new Table('histogramPETable', env('PROMETHEUSEXPORTER_HISTOGRAM_LINE'), $histogramStruct);
        $this->histogramTable->create();

    }


    /**
     * @return Table
     */
    public function getCounterTable(): Table
    {

        return $this->counterTable;
    }

    /**
     * @return Table
     */
    public function getGaugeTable(): Table
    {
        return $this->gaugeTable;
    }

    /**
     * @return Table
     */
    public function getHistogramTable(): Table
    {
        return $this->histogramTable;
    }



    public function beans()
    {
        return [];
    }


}