<?php

namespace ApiBundle\Api\Resource\DragCaptcha;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DragCaptcha extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $result = $this->getBizDragCaptcha()->generate();
        $result['url'] = $this->generateUrl('drag_captcha', array('token' => $result['token']), true);

        return $result;
    }

    /**
     * @return \Biz\Common\BizCaptcha
     */
    private function getBizDragCaptcha()
    {
        return $this->biz['biz_drag_captcha'];
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
            'status' => $this->getBizDragCaptcha()->check($captchaId, $phrase),
        );
    }
}
