<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\KeyApplier;

class KeyApplierTest extends BaseTestCase
{
    public function testApplyKey()
    {
        $applier = new KeyApplier();
        $result = $applier->applyKey($this->getCurrentUser(), 'opensource', 'apply', 1);
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
        ), $result['params']);

        $this->assertArrayEquals(array(
            'connectTimeout' => 20,
            'userAgent' => 'EduSoho Install Client 1.0',
            'timeout' => 20,
            'headers' => array(
            0 => 'Content-type: application/json',
            1 => 'Sign: bd7eb87d90411c8a228dbd06abd000f4',
            ),
        ), $result['curlOptions']);
    }
}
