<?php

return [
    'app_name' => 's2b2c_m_plumber',
    'queues' => [
        'example_queue' => [
            'type' => 'beanstalk',
            'enable_queue' => true,
            'host' => '127.0.0.1',
            'port' => 11300,
            'password' => '',
        ],
        'crontab_job_queue' => [
            'type' => 'beanstalk',
            'enable_queue' => true,
            'host' => '127.0.0.1',
            'port' => 11300,
            'password' => '',
        ],
    ],
    'workers' => [
        'worker_1' => [
            'topic' => 'merchant.example',
            'num' => 1,
            'class' => 'AppBundle\Worker\ExampleWorker',
            'queue' => 'example_queue',
        ],
        'crontab_job_worker' => [
            'topic' => 'crontab_job_worker',
            'num' => 1,
            'class' => 'AppBundle\Worker\CrontabJobWorker',
            'queue' => 'crontab_job_queue',
        ],
    ],
];
