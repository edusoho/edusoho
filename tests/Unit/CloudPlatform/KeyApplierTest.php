<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\KeyApplier;
use AppBundle\Common\ReflectionUtils;

class KeyApplierTest extends BaseTestCase
{
    public function testApplyKey()
    {
        $applier = new KeyApplier();
        ReflectionUtils::setProperty($applier, 'moked', true);
        $user = $this->getCurrentUser();
        $user['visitorId'] = 'visitorId1234';
        $result = $applier->applyKey($user, 'opensource', 'apply', 1);

        $this->assertEquals('http://api.edusoho.net/v1/keys', $result['url']);
        $this->assertArrayEquals(array(
            'siteName' => 'EduSoho网络课程',
            'siteUrl' => 'http://test.com',
            'email' => 'admin@admin.com',
            'contact' => '',
            'qq' => '',
            'mobile' => '',
            'edition' => 'opensource',
            'source' => 'apply',
            'visitorId' => 'visitorId1234'
        ), $result['params']);

        $this->assertArrayEquals(array(
            'connectTimeout' => 20,
            'userAgent' => 'EduSoho Install Client 1.0',
            'timeout' => 20,
            'headers' => array(
            0 => 'Content-type: application/json',
            1 => 'Sign: d5bd7d78a5cb4f46dfa15f2d8a5de0c0',
            ),
        ), $result['curlOptions']);
    }
}
