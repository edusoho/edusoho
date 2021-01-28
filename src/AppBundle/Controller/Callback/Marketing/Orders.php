<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use Codeages\Weblib\Auth\Authentication;

class Orders extends MarketingBase
{
    public function accept(Request $request)
    {
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $logger->info('微营销通知处理课程订单');
        $content = $request->getContent();
        $postData = json_decode($content, true);

        $keyProvider = new AuthKeyProvider();
        $authentication = new Authentication($keyProvider);
        try {
            $logger->info('准备验证auth');
            $authentication->auth($request);

            $logger->info('验证请求的auth通过，请求认定为合法，处理相应逻辑');

            return $this->getMarketingCourseService()->join($postData);
        } catch (\Exception $e) {
            $logger->error($e);

            return array('code' => 'error', 'msg' => 'ES处理微营销订单失败,'.$e->getMessage());
        }
    }

    protected function getMarketingCourseService()
    {
        return $this->createService('Marketing:MarketingCourseService');
    }
}
