<?php

namespace ApiBundle\Api\Resource\DragCaptcha;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use AppBundle\Common\ArrayToolkit;

class DragCaptcha extends AbstractResource
{
    private $limitTypes = array(
        'web_register',
        'bind_register',
        'reset_password',
        'mobile_register',
        'mobile_reset_password',
        'sms_login',
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        $fields = ArrayToolkit::parts($fields, array(
            'times',
        ));

        $limitType = $request->request->get('limitType');
        $limitType = $this->getLimitType($limitType);
        $limitKey = $limitType.'_'.$request->getHttpRequest()->getClientIp();

        $result = $this->getBizDragCaptcha()->generate($fields, $limitKey);
        $result['url'] = $this->generateUrl('drag_captcha', array('token' => $result['token']), true);

        return $result;
    }

    private function getLimitType($limitType)
    {
        if (!in_array($limitType, $this->limitTypes)) {
            $limitType = 'default';
        }

        return $limitType;
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
