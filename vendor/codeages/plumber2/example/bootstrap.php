<?php

date_default_timezone_set('Asia/Shanghai');

$options = [
    'app_name' => 'ExampleWorker',
    'queues' => [
        'queue_server_1' => [
            'type' => 'beanstalk',
            'host' => '127.0.0.1',
            'port' => '11300'
        ],
        'queue_server_2' => [
            'type' => 'redis',
            'host' => '127.0.0.1',
            'port' => '6379'
        ]
    ],
    'workers' => [
        [
            'class' => 'Codeages\Plumber\Example\Example1Worker',
            'num' => 1,
            'queue' => 'queue_server_1',
            'topic' => 'test_beanstalk_topic',
            'rate_limit' => 'example_rate_limit',
            'hour_limit' => 'example_hour_limit',
        ],
        [
            'class' => 'Codeages\Plumber\Example\Example2Worker',
            'num' => 1,
            'queue' => 'queue_server_2',
            'topic' => 'test_redis_topic',
        ]
    ],
    'rate_limits' => [
        'example_rate_limit' => [
            'storage' => 'redis',
            'redis' => ['host' => '127.0.0.1', 'port' => '6379', 'password' => null],
            'allowance' => 5, // 限制每60秒最多消费1000个
            'period' => 60,
        ]
    ],
    'hour_limits' => [
        'example_hour_limit' => ['start' => 23, 'end' => 7], // 允许在 晚上23:00点 ~ 早上7:00点 执行 Job。（注意需要在启动脚本开头设置时区）
    ],
    'log_path' => __DIR__ . '/plumber.log',
    'pid_path' => __DIR__ . '/plumber.pid',
];

class ExampleContainer implements \Psr\Container\ContainerInterface
{
    public function get($id)
    {
        // TODO: Implement get() method.
    }

    public function has($id)
    {
        // TODO: Implement has() method.
    }
}

return [
    'options' => $options,
    'container' => new ExampleContainer(),
];