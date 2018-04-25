<?php

namespace Tests\Unit\CloudPlatform\Client;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Mockery;
use Biz\CloudPlatform\Client\EduSohoOpenClient;

class EdusohoOpenClientTest extends BaseTestCase
{
    public function testGetArticles()
    {
        $client = new EduSohoOpenClient();
        $content = $client->getArticles();
        $this->assertTrue(empty($content['error']));
    }

    public function testGetNotices()
    {
        $client = new EduSohoOpenClient();
        $content = $client->getNotices();
        $this->assertTrue(empty($content['error']));
    }
}