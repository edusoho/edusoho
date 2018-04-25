<?php

namespace ApiBundle\Api\Resource\Captcha;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
            throw new BadRequestHttpException('Missing Params', ErrorCode::INVALID_ARGUMENT);
        }

        return array(
            'status' => $this->getBizCaptcha()->check($captchaId, $phrase),
        );
    }
}
