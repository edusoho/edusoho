<?php

namespace MarketingMallBundle\Api\Resource\MallWechatNotification;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\MallWechatNotification\Service\MallWechatNotificationService;

class MallWechatNotification extends BaseResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request)
    {
        $body = $request->request->all();
        if (!ArrayToolkit::requireds($body, ['event', 'data'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->getMallWechatNotificationService()->notify($body['event'], json_decode($body['data'], true));

        return ['success' => true];
    }

    /**
     * @return MallWechatNotificationService
     */
    private function getMallWechatNotificationService()
    {
        return $this->service('MarketingMallBundle:MallWechatNotification:MallWechatNotificationService');
    }
}
