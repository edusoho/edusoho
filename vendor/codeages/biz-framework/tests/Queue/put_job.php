<?php

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

$biz = require dirname(dirname(__DIR__)).'/biz-console.php';

use Tests\Fixtures\QueueJob\ExampleFailedRetryJob;

$body = array('name' => 'example job');
$job = new ExampleFailedRetryJob($body);

$biz->service('Queue:QueueService')->pushJob($job);
