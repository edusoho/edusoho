<?php

namespace QiQiuYun\SDK\Tests;

use PHPUnit\Framework\TestCase;
use QiQiuYun\SDK\HttpClient\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ClientTest extends TestCase
{
    public function testRequest()
    {
        // $logger = new Logger('name');
        // $logger->pushHandler(new StreamHandler(dirname(dirname(__DIR__)).'/var/log/unittest.log'));
        $logger = null;

        $client = new Client(array(), $logger);
        $response = $client->request('GET', 'https://www.baidu.com/');
        $this->assertInstanceOf('QiQiuYun\\SDK\\HttpClient\\Response', $response);
    }
}
