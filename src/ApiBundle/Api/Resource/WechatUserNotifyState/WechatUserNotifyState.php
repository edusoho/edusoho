<?php

namespace ApiBundle\Api\Resource\WechatUserNotifyState;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;
use Biz\WeChat\WeChatException;

class WechatUserNotifyState extends AbstractResource
{
    public function get(ApiRequest $request, $type)
    {
        if ('official' != $type) {
            throw CommonException::ERROR_PARAMETER();
        }
        $weChatNotifyEnabled = $this->getSettingService()->node('wechat.wechat_notification_enabled', 0);

        if (!$weChatNotifyEnabled) {
            throw WeChatException::NOTIFY_SETTING_NOT_ENABLED();
        }
        $user = $this->getCurrentUser();
        $weChatUser = $this->getWeChatService()->getOfficialWeChatUserByUserId($user['id']);
        if (empty($weChatUser)) {
            return array(
                'bind' => false,
                'subscribe' => false,
            );
        }

        $result = array(
            'bind' => true,
            'subscribe' => false,
        );
        $data = $weChatUser['data'];
        if (!empty($data['subscribe'])) {
            $result['subscribe'] = true;
        }

        return $result;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->biz->service('WeChat:WeChatService');
    }
}
