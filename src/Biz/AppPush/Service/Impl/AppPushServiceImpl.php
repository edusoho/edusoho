<?php

namespace Biz\AppPush\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AppPush\Service\AppPushService;
use Biz\BaseService;
use Biz\Common\CommonException;

class AppPushServiceImpl extends BaseService implements AppPushService
{
    public function bindDevice($params)
    {
        $result = $this->getAppPushService()->inspectTenant();
        if ('ok' != $result['status']) {
            $this->getAppPushService()->enableTenant();
        }

        $this->getAppPushService()->bindDevice($params);
    }

    public function unbindDevice($userId)
    {
        $result = $this->getAppPushService()->inspectTenant();
        if ('ok' != $result['status']) {
            $this->getAppPushService()->enableTenant();
        }
        $this->getAppPushService()->unbindDevice($userId);
    }

    public function addTags($userId, $tags)
    {
        return $this->getAppPushService()->addTags($userId, $tags);
    }

    public function batchAddTags($userIds, $tag)
    {
        return $this->getAppPushService()->batchAddTags($userIds, $tag);
    }

    public function deleteTags($userId, $tags)
    {
        return $this->getAppPushService()->deleteTags($userId, $tags);
    }

    public function batchDeleteTags($userIds, $tag)
    {
        return $this->getAppPushService()->batchDeleteTags($userIds, $tag);
    }

    public function sendToTag($tag, $params)
    {
        if (!ArrayToolkit::requireds($params, ['title', 'message', 'category', 'extra'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getAppPushService()->sendToTag($tag, $params);
    }

    public function sendToUser($userId, $params)
    {
        if (!ArrayToolkit::requireds($params, ['title', 'message', 'category', 'extra'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getAppPushService()->sendToUser($userId, $params);
    }

    public function sendToUsers($userIds, $params)
    {
        if (!ArrayToolkit::requireds($params, ['title', 'message', 'category', 'extra'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getAppPushService()->sendToUsers($userIds, $params);
    }

    /**
     * @return \ESCloud\SDK\Service\AppPushService
     */
    private function getAppPushService()
    {
        return $this->biz['ESCloudSdk.appPush'];
    }
}
