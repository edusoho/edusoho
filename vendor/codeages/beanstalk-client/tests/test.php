#!/usr/bin/env php
<?php

use Codeages\Beanstalk\Client;

require_once __DIR__.'/../vendor/autoload.php';

$beanstalk = new Client(['socket_timeout' => 20]);

$beanstalk->connect();
$beanstalk->useTube('Example3');


while (true) {
    $job = $beanstalk->reserve(10);
    var_dump($job);
}


