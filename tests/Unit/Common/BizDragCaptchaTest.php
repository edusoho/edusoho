<?php

namespace Tests\Unit\Common;

use Biz\BaseTestCase;
use Biz\Common\BizDragCaptcha;

class BizDragCaptchaTest extends BaseTestCase
{
    public function testGenerate()
    {
        $result = $this->getBizDragCaptcha()->generate(array());

        $this->assertNotNull($result['token']);
        $this->assertNotNull($result['jigsaw']);

        $result = $this->getBizDragCaptcha()->generate(array(), 'test');

        $this->assertNotNull($result['token']);
        $this->assertNotNull($result['jigsaw']);
    }

    public function testGetBackground()
    {
        $result = $this->getBizDragCaptcha()->getBackground('test');

        $this->assertNull($result);

        $token = $this->getBizDragCaptcha()->generate(array());
        $result = $this->getBizDragCaptcha()->getBackground($token['token']);

        $this->assertNotFalse(strpos($result, 'gd-jpeg'));
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_drag_captcha_required
     */
    public function testCheckErrorWithEmptyDragToken()
    {
        $this->getBizDragCaptcha()->check('');
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_drag_captcha_required
     */
    public function testCheckErrorWithNotArrayToken()
    {
        $this->getBizDragCaptcha()->check('test');
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_drag_captcha_expired
     */
    public function testCheckErrorWithEmptyToken()
    {
        $dragToken = array('token' => 'test', 'captcha' => 'test');
        $dragToken = strrev(base64_encode(json_encode($dragToken)));
        $this->getBizDragCaptcha()->check($dragToken);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_drag_captcha_error
     */
    public function testCheckWithErrorCaptcha()
    {
        $token = $this->getBizDragCaptcha()->generate(array());
        $dragToken = array('token' => $token['token'], 'captcha' => 1000);
        $dragToken = strrev(base64_encode(json_encode($dragToken)));
        $this->getBizDragCaptcha()->check($dragToken);
    }

    /**
     * @return BizDragCaptcha
     */
    private function getBizDragCaptcha()
    {
        return $this->biz['biz_drag_captcha'];
    }
}
