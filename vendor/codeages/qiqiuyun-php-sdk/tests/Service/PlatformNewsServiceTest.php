<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Constants\PlatformNewsBlockTypes;
use QiQiuYun\SDK\Service\PlatformNewsService;
use QiQiuYun\SDK\Tests\BaseTestCase;

class PlatformNewsServiceTest extends BaseTestCase
{
    private $advice = array(
        'id' => PlatformNewsBlockTypes::ADVICE_BLOCK,
        'name' => '经营建议',
        'returnUrl' => 'http://test.edusoho.com/test1',
        'details' => array(
            array(
                'title' => '课程A',
                'image' => 'https://test.com/image1',
                'url' => 'https://test.com/ad',
                'subtitle' => '本课程为xxx课程',
                'position' => 1,
            ),
            array(
                'title' => '课程B',
                'image' => 'https://test.com/image2',
                'url' => 'https://test.com/ad',
                'subtitle' => '本课程为ccc课程',
                'position' => 2,
            ),
            array(
                'title' => '课程C',
                'image' => 'https://test.com/image3',
                'url' => 'https://test.com/ad',
                'subtitle' => '本课程为zzz课程',
                'position' => 3,
            ),
            array(
                'title' => '课程D',
                'image' => 'https://test.com/image4',
                'url' => 'https://test.com/ad',
                'subtitle' => '本课程为vvv课程',
                'position' => 4,
            ),
        ),
    );

    private $plugin = array(
        'id' => PlatformNewsBlockTypes::PLUGIN_BLOCK,
        'name' => '应用简介',
        'returnUrl' => 'http://test.edusoho.com/test2',
        'details' => array(
            array(
                'title' => '应用A',
                'image' => 'https://test.com/image1',
                'url' => 'https://test.com/ad',
                'subtitle' => '本应用为xxx应用',
                'position' => 1,
            ),
            array(
                'title' => '应用B',
                'image' => 'https://test.com/image2',
                'url' => 'https://test.com/ad',
                'subtitle' => '本应用为ccc应用',
                'position' => 2,
            ),
            array(
                'title' => '应用C',
                'image' => 'https://test.com/image3',
                'url' => 'https://test.com/ad',
                'subtitle' => '本应用为zzz应用',
                'position' => 3,
            ),
            array(
                'title' => '应用D',
                'image' => 'https://test.com/image4',
                'url' => 'https://test.com/ad',
                'subtitle' => '本应用为vvv应用',
                'position' => 4,
            ),
        ),
    );

    private $announcement = array(
        'id' => PlatformNewsBlockTypes::ANNOUNCEMENT_BLOCK,
        'name' => '站长公告',
        'returnUrl' => 'http://test.edusoho.com/test3',
        'details' => array(
            array(
                'title' => '公告1',
                'image' => 'https://test.com/image1',
                'url' => 'https://test.com/ad',
                'subtitle' => '公告1',
                'position' => 1,
            ),
            array(
                'title' => '公告2',
                'image' => 'https://test.com/image2',
                'url' => 'https://test.com/ad',
                'subtitle' => '公告2',
                'position' => 2,
            ),
            array(
                'title' => '公告3',
                'image' => 'https://test.com/image3',
                'url' => 'https://test.com/ad',
                'subtitle' => '公告3',
                'position' => 3,
            ),
            array(
                'title' => '公告4',
                'image' => 'https://test.com/image4',
                'url' => 'https://test.com/ad',
                'subtitle' => '公告4',
                'position' => 4,
            ),
        ),
    );

    private $qrCode = array(
        'id' => PlatformNewsBlockTypes::QR_CODE_BLOCK,
        'name' => '公众号',
        'returnUrl' => 'http://test.edusoho.com/test4',
        'details' => array(
            array(
                'title' => '公众号1',
                'image' => 'https://test.com/image1',
                'url' => 'https://test.com/ad',
                'subtitle' => '公众号1',
                'position' => 1,
            ),
            array(
                'title' => '公众号2',
                'image' => 'https://test.com/image2',
                'url' => 'https://test.com/ad',
                'subtitle' => '公众号2',
                'position' => 2,
            ),
        ),
    );

    public function testGetAdvice()
    {
        $limit = 2;

        $expectedValue = $this->advice;
        $expectedValue['details'] = array_slice($this->advice['details'], 0, $limit);

        $client = $this->mockHttpClient($expectedValue);

        $result = $this->getPlatformNewsService($client)->getAdvice($limit);

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($expectedValue['details'], $result['details']);

        $client = $this->mockHttpClient($this->advice);

        $result = $this->getPlatformNewsService($client)->getAdvice();

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($this->advice['details'], $result['details']);
    }

    public function testGetApplications()
    {
        $limit = 1;

        $expectedValue = $this->plugin;
        $expectedValue['details'] = array_slice($this->plugin['details'], 0, $limit);

        $client = $this->mockHttpClient($expectedValue);

        $result = $this->getPlatformNewsService($client)->getApplications($limit);

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($expectedValue['details'], $result['details']);

        $client = $this->mockHttpClient($this->plugin);

        $result = $this->getPlatformNewsService($client)->getApplications();

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($this->plugin['details'], $result['details']);
    }

    public function testGetAnnouncements()
    {
        $limit = 1;

        $expectedValue = $this->announcement;
        $expectedValue['details'] = array_slice($this->announcement['details'], 0, $limit);

        $client = $this->mockHttpClient($expectedValue);

        $result = $this->getPlatformNewsService($client)->getAnnouncements($limit);

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($expectedValue['details'], $result['details']);

        $result = $this->getPlatformNewsService($client)->getAnnouncements();

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($expectedValue['details'], $result['details']);
    }

    public function testGetQrCode()
    {
        $client = $this->mockHttpClient($this->qrCode);

        $result = $this->getPlatformNewsService($client)->getQrCode(2);

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($this->qrCode['details'], $result['details']);

        $expectedValue = $this->qrCode;
        $expectedValue['details'] = array_slice($this->qrCode['details'], 0, 1);

        $client = $this->mockHttpClient($expectedValue);

        $result = $this->getPlatformNewsService($client)->getQrCode();

        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($expectedValue['details'], $result['details']);
    }

    private function getPlatformNewsService($client)
    {
        return new PlatformNewsService($this->auth, array(), null, $client);
    }
}
