#!/usr/bin/env php
<?php

use Codeages\Beanstalk\Client as BeanstalkClient;

require_once __DIR__.'/../vendor/autoload.php';

$config = [];
$config['host'] = getenv('QUEUE_HOST') ? : '127.0.0.1';
$config['port'] = getenv('QUEUE_PORT') ? : 11300;
$config['persistent'] = false;

$beanstalk = new BeanstalkClient($config);

$beanstalk->connect();
$beanstalk->useTube('test_beanstalk_topic');

$result = $beanstalk->put(
    500, // Give the job a priority of 23.
    0,  // Do not wait to put job into the ready queue.
    60, // Give the job 1 minute to run.
    'hello'
);

$beanstalk->disconnect();
