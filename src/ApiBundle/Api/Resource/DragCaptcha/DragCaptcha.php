<?php

namespace ApiBundle\Api\Resource\DragCaptcha;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Common\ArrayToolkit;

class DragCaptcha extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        $fields = ArrayToolkit::parts($fields, array(
            'times'
        ));

        $limitType = $request->request->get('limitType', 'default');
        $limitKey = $limitType.'_'.$request->getHttpRequest()->getClientIp();

        $result = $this->getBizDragCaptcha()->generate($fields, $limitKey);
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
    public function get(ApiRequest $request, $token)
    {
        return array(
            'status' => $this->getBizDragCaptcha()->check($token),
        );
    }
}
