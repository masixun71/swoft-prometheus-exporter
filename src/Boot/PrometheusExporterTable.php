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
use Swoft\Memory\Table;
use Swoole\Lock;


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

    private $lock;


    public function __construct()
    {
        $counterStruct = [
            'metricName'   => [Table::TYPE_STRING, 100],
            'labelString'  => [Table::TYPE_STRING, 255],
            'value' => [Table::TYPE_INT, Table::EIGHT_INT_LENGTH],
            'updateTime'  => [Table::TYPE_STRING, 15],
            'help' => [Table::TYPE_STRING, 255],
        ];
        $this->counterTable = new Table('counterPETable', 1024, $counterStruct);
        $this->counterTable->create();

        $gaugeStruct = [
            'metricName'   => [Table::TYPE_STRING, 100],
            'labelString'  => [Table::TYPE_STRING, 255],
            'value' => [Table::TYPE_STRING, 255],
            'updateTime'  => [Table::TYPE_STRING, 15],
            'help' => [Table::TYPE_STRING, 255],
        ];
        $this->gaugeTable = new Table('gaugePETable', 1024, $gaugeStruct);
        $this->gaugeTable->create();

        $histogramStruct = [
            'metricName'   => [Table::TYPE_STRING, 100],
            'labelString'  => [Table::TYPE_STRING, 255],
            'value' => [Table::TYPE_FLOAT, 8],
            'updateTime'  => [Table::TYPE_STRING, 15],
            'help' => [Table::TYPE_STRING, 255],
        ];
        $this->histogramTable = new Table('histogramPETable', 1024, $histogramStruct);
        $this->histogramTable->create();

        $this->lock = new Lock(SWOOLE_MUTEX);
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

    public function getLock(): Lock
    {
        return $this->lock;
    }


    public function beans()
    {
        return [];
    }


}