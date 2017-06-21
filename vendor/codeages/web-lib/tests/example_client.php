<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification;

$config = array(
    'accessKey' => 'test_key_id_1',
    'secretKey' => 'test_key_secret_1',
    'endpoint' => 'http://localhost:8000',
);

$spec = new JsonHmacSpecification('sha1');

$client = new RestApiClient($config, $spec);

$result = $client->get('/');

var_dump($result);