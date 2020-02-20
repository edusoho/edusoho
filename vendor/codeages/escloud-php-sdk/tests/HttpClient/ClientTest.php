<?php

namespace ESCloud\SDK\Tests;

use PHPUnit\Framework\TestCase;
use ESCloud\SDK\HttpClient\Client;
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
        $this->assertInstanceOf('ESCloud\\SDK\\HttpClient\\Response', $response);
    }
}
