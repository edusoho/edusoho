<?php

namespace Tests\Unit\Common;

use Biz\BaseTestCase;
use Biz\Common\BizCaptcha;

class BizCaptchaTest extends BaseTestCase
{
    public function testGenerate()
    {
        $result = $this->getBizCaptcha()->generate();

        $this->assertNotNull($result);
    }

    public function testCheck()
    {
        $result = $this->getBizCaptcha()->generate();
        $status = $this->getBizCaptcha()->check($result['captchaToken'], '0000');

        $this->assertEquals(BizCaptcha::STATUS_INVALID, $status);
    }

    /**
     * @return BizCaptcha
     */
    private function getBizCaptcha()
    {
        return $this->biz['biz_captcha'];
    }

}
