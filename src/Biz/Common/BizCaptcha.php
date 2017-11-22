<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\BizAware;

class BizCaptcha extends BizAware
{
    const STATUS_SUCCESS = 'success';

    const STATUS_INVALID = 'invalid';

    const STATUS_EXPIRED = 'expired';

    private $tokenType = 'captcha';

    /**
     * @var \Gregwar\Captcha\CaptchaBuilder
     */
    protected $captchaBuilder;

    public function generate($options = array())
    {
        $options = array_merge(array('width' => 150, 'height' => 32, 'quality' => 90, 'verify_times' => 10), $options);
        $this->captchaBuilder->build($options['width'], $options['height'], null, null);

        $token = $this->getTokenService()->makeToken($this->tokenType, array(
            'times' => $options['verify_times'],
            'duration' => 60 * 30,
            'userId' => 0,
            'data' => array(
                'phrase' => $this->captchaBuilder->getPhrase(),
            ),
        ));

        return array('image' => $this->captchaBuilder->inline($options['quality']), 'captchaToken' => $token['token']);
    }

    public function check($captchaId, $phrase)
    {
        $token = $this->getTokenService()->verifyToken($this->tokenType, $captchaId);

        if (empty($token)) {
            return self::STATUS_INVALID;
        }

        $remainedTimes = $token['remainedTimes'];

        if (0 == $remainedTimes) {
            return self::STATUS_EXPIRED;
        }

        return $token['data']['phrase'] == $phrase ? self::STATUS_SUCCESS : self::STATUS_INVALID;
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
