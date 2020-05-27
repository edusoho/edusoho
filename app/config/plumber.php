<?php

return [
    'app_name' => 's2b2c_m_plumber',
    'queues' => [
        'beanstalk_queue' => [
            'queue_options' => 'default',
        ],
        'redis_queue' => [
            'queue_options' => 'redis_default',
        ],
    ],
    'workers' => [
        'crontab_job_worker' => [
            'topic' => 'crontab_job_worker',
            'num' => 1,
            'class' => 'AppBundle\Worker\CrontabJobWorker',
            'queue' => 'beanstalk_queue',
        ],
        'update_product_worker' => [
            'topic' => 'update_product_worker',
            'num' => 1,
            'class' => 'AppBundle\Worker\UpdateProductWorker',
            'queue' => 'beanstalk_queue',
        ],
    ],
    'log_path' => __DIR__.'/../logs/plumber.log',
    'pid_path' => __DIR__.'/../data/plumber.pid',
];
