<?php

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);

$redis->lPush('test_redis_topic', 'message 1');