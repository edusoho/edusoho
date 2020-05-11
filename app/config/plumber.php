<?php

return [
    'app_name' => 's2b2c_m_plumber',
    'queues' => [
        'crontab_job_queue' => [
            'queue_options' => 'default',
        ],
    ],
    'workers' => [
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
