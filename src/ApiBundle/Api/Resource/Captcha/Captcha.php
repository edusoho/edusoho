<?php

namespace ApiBundle\Api\Resource\Captcha;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\CommonException;

class Captcha extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        return $this->getBizCaptcha()->generate();
    }

    /**
     * @return \Biz\Common\BizCaptcha
     */
    private function getBizCaptcha()
    {
        return $this->biz['biz_captcha'];
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $captchaId)
    {
        if (!($phrase = $request->query->get('phrase'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return array(
            'status' => $this->getBizCaptcha()->check($captchaId, $phrase),
        );
    }
}
