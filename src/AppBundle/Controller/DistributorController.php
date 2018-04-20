<?php

namespace AppBundle\Controller;

use Biz\Distributor\Common\DistributorCookieToolkit;
use Symfony\Component\HttpFoundation\Request;

class DistributorController extends BaseController
{
    /**
     * 分销平台分享后，进入的注册页面，需要记录token 到 cookie， 注册成功后，清除
     */
    public function registerAction(Request $request)
    {
        $fields = $request->query->all();
        $registerUrl = $this->generateUrl('register');
        if (!empty($fields['token'])) {
            if ($this->getCurrentUser()->isLogin()) {
                $response = $this->redirect($this->generateUrl('logout').'?goto='.$registerUrl);
            } else {
                $response = $this->redirect($registerUrl);
            }
            $response = DistributorCookieToolkit::setTokenToCookie($response, $fields['token'], 'user');

            return $response;
        }

        return $this->redirect($registerUrl);
    }

    public function productAction(Request $request)
    {
        $fields = $request->query->all();
        $loginUrl = $this->generateUrl('login');
        if (!empty($fields['token'])) {
            list($routingName, $routingParams) = $this->DistributorOrderService()->getRoutingInfo($fields['token']);
            $productUrl = $this->generateUrl($routingName, $routingParams);

            if ($this->getCurrentUser()->isLogin()) {
                $response = $this->redirect($productUrl);
            } else {
                $response = $this->redirect($loginUrl.'?goto='.$productUrl);
            }
            $response = DistributorCookieToolkit::setTokenToCookie($response, $fields['token'], 'course', 0); //cookie 随浏览器关闭而失效

            return $response;
        }

        return $this->redirect($loginUrl);
    }

    protected function DistributorOrderService()
    {
        return $this->createService('Distributor:DistributorOrderService');
    }
}
