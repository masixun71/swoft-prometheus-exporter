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

    /**
     * @var null|Table
     */
    private $counterTable = null;

    /**
     * @var null|Table
     */
    private $gaugeTable = null;

    /**
     * @var null|Table
     */
    private $histogramTable = null;


    public function __construct()
    {
        (new LoadEnv())->bootstrap();

        $prometheusexporterCounterLine = env('PROMETHEUSEXPORTER_COUNTER_LINE');
        if ($prometheusexporterCounterLine > 0) {
            $counterStruct = [
                'metricName'   => [Table::TYPE_STRING, 50],
                'labelString'  => [Table::TYPE_STRING, 255],
                'value' => [Table::TYPE_INT, Table::EIGHT_INT_LENGTH],
                'updateTime'  => [Table::TYPE_STRING, 15],
                'help' => [Table::TYPE_STRING, 30],
            ];
            $this->counterTable = new Table('counterPETable', $prometheusexporterCounterLine, $counterStruct);
            $this->counterTable->create();
        }
        $prometheusExporterGaugeLine = env('PROMETHEUSEXPORTER_GAUGE_LINE');
        if ($prometheusExporterGaugeLine > 0 ) {
            $gaugeStruct = [
                'metricName'   => [Table::TYPE_STRING, 50],
                'labelString'  => [Table::TYPE_STRING, 255],
                'value' => [Table::TYPE_STRING, 255],
                'updateTime'  => [Table::TYPE_STRING, 15],
                'help' => [Table::TYPE_STRING, 30],
            ];
            $this->gaugeTable = new Table('gaugePETable', $prometheusExporterGaugeLine, $gaugeStruct);
            $this->gaugeTable->create();
        }
        $prometheusExporterHistogramLine = env('PROMETHEUSEXPORTER_HISTOGRAM_LINE');
        if ($prometheusExporterHistogramLine > 0) {
            $histogramStruct = [
                'metricName'   => [Table::TYPE_STRING, 50],
                'labelString'  => [Table::TYPE_STRING, 255],
                'value' => [Table::TYPE_FLOAT, 8],
                'updateTime'  => [Table::TYPE_STRING, 15],
                'help' => [Table::TYPE_STRING, 30],
            ];
            $this->histogramTable = new Table('histogramPETable', $prometheusExporterHistogramLine, $histogramStruct);
            $this->histogramTable->create();
        }
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