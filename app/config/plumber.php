<?php

return [
    'app_name' => 's2b2c_m_plumber',
    'queues' => [
        'example_queue' => [
            'enable_queue' => true,
            'queue_options' => 'redis_default',
        ],
        'crontab_job_queue' => [
            'enable_queue' => true,
            'queue_options' => 'default',
        ],
    ],
    'workers' => [
        'worker_1' => [
            'topic' => 'merchant.example',
            'num' => 1,
            'class' => 'AppBundle\Worker\ExampleWorker',
            'queue' => 'example_queue',
            'hour_limit' => 'default_hour_limit',
        ],
        'crontab_job_worker' => [
            'topic' => 'crontab_job_worker',
            'num' => 1,
            'class' => 'AppBundle\Worker\CrontabJobWorker',
            'queue' => 'crontab_job_queue',
        ],
    ],
    'log_path' => __DIR__.'/../logs/plumber.log',
    'pid_path' => __DIR__.'/../data/plumber.pid',
];
