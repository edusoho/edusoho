<?php
namespace QiQiuYun\SDK\Tests;

use PHPUnit\Framework\TestCase;
use QiQiuYun\SDK\HttpClient\Client;

class ClientTest extends TestCase
{
    public function testRequest()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://www.baidu.com');
        $this->assertInstanceOf("QiQiuYun\\SDK\\HttpClient\\Response", $response);
    }
}
