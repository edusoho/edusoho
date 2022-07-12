<?php

namespace ApiBundle\Api\Resource\DragCaptcha;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DragCaptcha extends AbstractResource
{
    private $limitTypes = [
        'web_register',
        'bind_register',
        'reset_password',
        'mobile_register',
        'mobile_reset_password',
        'sms_login',
        'user_login',
    ];

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        $fields = ArrayToolkit::parts($fields, [
            'times',
        ]);

        $limitType = $request->request->get('limitType');
        $limitType = $this->getLimitType($limitType);
        $limitKey = $limitType.'_'.$request->getHttpRequest()->getClientIp();

        $result = $this->getBizDragCaptcha()->generate($fields, $limitKey);
        $result['url'] = $this->generateUrl('drag_captcha', ['token' => $result['token']], UrlGeneratorInterface::ABSOLUTE_URL);

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
        $flag = $this->getBizDragCaptcha()->check($token);
        if($flag){
            $dragTokens = empty($_SESSION["dragTokens"]) ? array() : $_SESSION["dragTokens"];
            $dragTokens[] = $token;
            $session = $request->getHttpRequest()->getSession();
            $session->set("dragTokens", $dragTokens);
        }

        return [
            'status' => $flag,
        ];
    }
}
