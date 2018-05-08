<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\DrpService;

class DrpServiceTest extends BaseTestCase
{
    public function testPostData()
    {
    }

    public function testParseRegisterToken_normal()
    {
        $data = array(
            'agency_id' => '120', 
            'merchant_id' => '110', 
            'coupon_price' => '100', 
            'coupon_expiry_day' => '5');
        $nonce = 'abcedf';
        $time = time();
        ksort($data);
        $dataStr = json_encode($data);
        $signingText = implode("\n", array($nonce, $time, $dataStr));
        $sign =  $this->auth->makeSignature($signingText);

        $token = "{$data['merchant_id']}:{$data['agency_id']}:{$data['coupon_price']}:{$data['coupon_expiry_day']}:{$time}:{$nonce}:{$sign}";
        $actualData = $this->getDrpService()->parseRegisterToken($token);

        $this->assertEquals($data['coupon_price'],$actualData['coupon_price']);
        $this->assertEquals($data['coupon_expiry_day'], $actualData['coupon_expiry_day']);
        $this->assertEquals($time, $actualData['time']);
        $this->assertEquals($nonce, $actualData['nonce']);
    }

    public function testParseCourseActivityToken_normal()
    {
        $data = array(
            'agency_id' => '120', 
            'merchant_id' => '110', 
            'course_id' => '100');
        $nonce = 'abcedf';
        $time = time();
        ksort($data);
        $dataStr = json_encode($data);
        $signingText = implode("\n", array($nonce, $time, $dataStr));
        $sign =  $this->auth->makeSignature($signingText);

        $token = "courseOrder:{$data['course_id']}:{$data['merchant_id']}:{$data['agency_id']}:{$time}:{$nonce}:{$sign}";
        $actualData = $this->getDrpService()->parseCourseActivityToken($token);

        $this->assertEquals('courseOrder', $actualData['distribution_type']);
        $this->assertEquals($data['course_id'], $actualData['course_id']);
        $this->assertEquals($time, $actualData['time']);
        $this->assertEquals($nonce, $actualData['nonce']);
    }

    private function getDrpService(){
        return new DrpService($this->auth);
    }
}
