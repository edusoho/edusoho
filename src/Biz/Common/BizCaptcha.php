<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\BizAware;

class BizCaptcha extends BizAware
{
    private $tokenType = 'captcha';

    /**
     * @var \Gregwar\Captcha\CaptchaBuilder
     */
    protected $captchaBuilder;

    public function generate($options = array())
    {
        $options = array_merge(array('width' => 150, 'height' => 32, 'quality' => 90, 'verify_times' => 2), $options);
        $this->captchaBuilder->build($options['width'], $options['height'],null, null);

        $token = $this->getTokenService()->makeToken($this->tokenType, array(
            'times'    => $options['verify_times'],
            'duration' => 60 * 30,
            'userId'   => 0,
            'data'     => array(
                'phrase' => $this->captchaBuilder->getPhrase()
            )
        ));

        return array('data' => $this->captchaBuilder->inline($options['quality']), 'captchaId' => $token['token']);
    }

    public function check($captchaId, $phrase)
    {
        $token = $this->getTokenService()->verifyToken($this->tokenType, $captchaId);

        if (empty($token)) {
            return false;
        }

        return $phrase == $token['data']['phrase'];
    }

    public function setCaptchaBuilder($captchaBuilder)
    {
        $this->captchaBuilder = $captchaBuilder;
    }

    /**
     * @return \Biz\User\Service\TokenService
     */
    private function getTokenService()
    {
        return $this->biz->service('User:TokenService');
    }
}
