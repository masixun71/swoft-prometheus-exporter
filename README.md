![](https://img.shields.io/badge/version-v0.0.0.7-red.svg)
![](https://img.shields.io/badge/php-%3E=7.1-orange.svg)
![](https://img.shields.io/badge/swoole-%3E=4.0-blue.svg)



# 简介
本项目基于github上的swoft开源项目进行组件开发，扩展了一个prometheusExporter sdk组件,
prometheus 是一个开源的系统监控和告警平台，通过Exporter即可快速地生成prometheus需要的记录信息，
通过中间件和注解来对监控数据非侵入式注入。

# 环境强制要求

1. 根据swoft官方要求即可



# 配置步骤

## 1.加载包
```php
   composer require extraswoft/prometheus-exporter
```


## 2.在base.php文件的middlewares里加入middleware
#### 注意：加入该中间件是对所有请求进行的基本监控，包括持久化。
```php
        'middlewares' => [
            InitPrometheusExporterMiddleware::class,
            ContextMiddleware::class,
            DevToolMiddleware::class,//http://127.0.0.1:20009/__devtool  php bin/swoft dev:publish swoft/devtool -f
            
        ]
```



## 3.在app.php文件的bootScan和beanScan加入bean需要扫描的命名空间
```php
    'bootScan' => [
            'ExtraSwoft\PrometheusExporter\Boot'
        ],
        
    'beanScan' => [
            "ExtraSwoft\\PrometheusExporter\\"
        ],    
```

## 4.在.env配置文件中添加以下配置
#####  PROMETHEUSEXPORTER_REDIS_PREFIX，持久化到redis的命名前缀
#####  PROMETHEUSEXPORTER_PUSHGATEWAY_HOST, 若采用pushGateway,它的地址
#####  PROMETHEUSEXPORTER_INSTANCE, 实例名，可以配置，也可以用主机名，适合多实例
#####  PROMETHEUSEXPORTER_COUNTER_LINE，swoole table 申请的行数，下两个同理
#####  PROMETHEUSEXPORTER_GAUGE_LINE，
#####  PROMETHEUSEXPORTER_HISTOGRAM_LINE，histogram需要的行数要较其他的较多，建议配置多点

```php
    PROMETHEUSEXPORTER_REDIS_PREFIX=www:www-api:cache:
    PROMETHEUSEXPORTER_PUSHGATEWAY_HOST=http://localhost:9091
    PROMETHEUSEXPORTER_INSTANCE=local
    PROMETHEUSEXPORTER_COUNTER_LINE=1024
    PROMETHEUSEXPORTER_GAUGE_LINE=1024
    PROMETHEUSEXPORTER_HISTOGRAM_LINE=4096

```

# 日常使用
## 1.注解使用（只支持方法）
### 注意，若想在Controller里的方法使用(普通的bean可以不关心)，需要同时给Controller添加注解，比如:
```php
        /**
         * @Controller(prefix="/test")
         * @PECounter()
         * @PEHistogram()
         * @PEGaugeAfter()
         * @PEGauge()
         */
         class TestController
         {
                /**
                 * @PECounter()
                 * @PEHistogram()
                 * @PEGaugeAfter()
                 * @PEGauge()
                 * @RequestMapping()
                 * @return string
                 */
                public function demo()
                {}
         }
```

```php
         @PECounter(namespace="test", name="demo", value=1, labels={"test":"ok"}, help="123")
         value是 调用改方法会增加或者减少的值
```
```php
         @PEHistogram(namespace="test", name="demo", labels={"test":"ok"}, help="123")
         该注解的作用是记录调用该方法的整个执行时间再进行Histogram
```
```php
         @PEGauge(namespace="test", name="demo", value=1, labels={"test":"ok"}, help="123")
         @PEGaugeAfter(namespace="test", name="demo", returnKey="data,aa", labels={"test":"ok"}, help="123")
         两者区别在于一个依赖默认值，一个依赖返回值
         After注解的方法返回值必须是个数组，逗号代表着层级
         
```

## 2.方法调用
```php
       /**
        * @Inject()
        * @var PECollectorRegistry
        */
       private $pECollectorRegistry;
       
       
        /**
         * @Inject()
         * @var PrometheusExporterTable
         */
       private $prometheusExporterTable;
       
       
         public function demo()
           {
               $this->collectorRegistry->counterIncr('test', 'demo', 1);
               $this->collectorRegistry->counterIncr('test', 'demo', 1);
               $this->collectorRegistry->counterIncr('test', 'demo', 1);
               $this->collectorRegistry->counterIncr('test', 'demo2', 1);
               $this->collectorRegistry->counterIncr('test', 'demo2', 1);
               $this->collectorRegistry->counterDecr('test', 'demo2', 1);
               $this->collectorRegistry->counterIncr('test', 'demo21', 1, ['test' => 'ok']);
               $this->collectorRegistry->counterIncr('test', 'demo22', 1, ['test' => 'ok', 'test2' => 'ok3'], 'this is good');
       
               $this->collectorRegistry->gaugeSet('test', 'demo3', "123", ['test' => 'ok']);
               $this->collectorRegistry->gaugeSet('test', 'demo4', "123", ['test' => 'ok']);
               $this->collectorRegistry->gaugeSet('test', 'demo3', "1234", ['test' => 'ok']);
       
               $this->collectorRegistry->histogramIncr('test', 'demo5', 0.03, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 0.1, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 8, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 11, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 0.03, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 0.1, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 8, ['test' => 'ok', 'kk' => 1]);
               $this->collectorRegistry->histogramIncr('test', 'demo5', 11, ['test' => 'ok', 'kk' => 1]);
       
       
       //        foreach($this->collectorRegistry->getCounters() as $key => $value)
       //        {
       //            $res = $this->prometheusExporterTable->getCounterTable()->get($key);
       //            var_dump($res);
       //        }
       //
       //        foreach($this->collectorRegistry->getGauges() as $key => $value)
       //        {
       //            $res = $this->prometheusExporterTable->getGaugeTable()->get($key);
       //            var_dump($res);
       //        }
       //
       //        foreach($this->collectorRegistry->getHistograms() as $key => $value)
       //        {
       //            $res = $this->prometheusExporterTable->getHistogramTable()->get($key);
       //            var_dump($res);
       //        }
           }
```

# 持久化
## 1.注解，给某个方法或接口加上缓存注解，调用即可，灵活方便（推荐）
```php
   @PECacheTable() 
```

## 2.自行使用方法
```php
   $this->collectorRegistry->cacheTable();
```

# 使用pushGateway
```php
   /**
    * @Inject()
    * @var PushGateway
    */
    private $pushGateway;

   $this->pushGateway->push($this->collectorRegistry, 'swoft', array('instance'=>env('PROMETHEUSEXPORTER_INSTANCE')));
   $this->pushGateway->push($this->collectorRegistry, 'swoft', array('instance'=>gethostname()));
```

# 获取prometheus文本
具体可参照下面例子

```php
    $this->collectorRegistry->getRender();
```

# 效果图
![image]()


# 问题
####1.prometheus怎么用，好用不，搭配grafana怎么用？
#### 答：好用，看完这本你都会了,[prometheus实战](https://songjiayang.gitbooks.io/prometheus/content/)


