<?php

namespace Biz\SCRM\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\CacheService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use ESCloud\SDK\Service\ScrmService;

class SCRMServiceImpl extends BaseService implements \Biz\SCRM\Service\SCRMService
{
    public function isSCRMBind()
    {
        $isScrmBind = $this->getCacheService()->get('scrm_bind');
        if (!isset($isScrmBind)) {
            $isScrmBind = 0;
            try {
                if($this->getSCRMSdk() != null){
                    $scrmBind = $this->getSCRMSdk()->isScrmBind();
                    $isScrmBind = empty($scrmBind['ok']) ? 0 : 1;
                }
            } catch (\Exception $e) {
                $isScrmBind = 0;
            }

            $this->getCacheService()->set('scrm_bind', $isScrmBind, time() + 7200);
        }

        return $isScrmBind;
    }

    public function setUserSCRMData($user)
    {
        if (!empty($user['scrmUuid'])) {
            return $user;
        }

        try {
            $userScrmData = $this->getSCRMSdk()->getCustomer($user['uuid']);
            $user = $this->getUserService()->setUserScrmUuid($user['id'], $userScrmData['customerUniqueId']);
            $this->getUserService()->updateUserProfile($user['id'], [
                'wechat_nickname' => urlencode($userScrmData['nickname']),
                'wechat_picture' => $userScrmData['avatar'],
            ]);
        } catch (\Exception $e) {
        }

        $this->dispatchEvent('scrm.user_bind', new Event($user));

        return $user;
    }

    public function setStaffSCRMData($user)
    {
        if (!empty($user['scrmUuid'])) {
            return $user;
        }

        try {
            $userScrmData = $this->getSCRMSdk()->getStaff($user['uuid']);
            !empty($userScrmData['staffId']) ? $user = $this->getUserService()->setUserScrmStaffId($user['id'], $userScrmData['staffId']) : null;
        } catch (\Exception $e) {
        }

        return $user;
    }

    public function getAssistantQrCode($assistant)
    {
        if (empty($assistant)) {
            return '';
        }

        try {
            $assistantData = $this->getScrmSdk()->getStaffQrCode($assistant['scrmStaffId']);
            $qrCodeUrl = $assistantData['qrCodeUrl'];
        } catch (\Exception $e) {
            $qrCodeUrl = empty($assistant['weChatQrCode']) ? '' : $assistant['weChatQrCode'];
        }

        return $qrCodeUrl;
    }

    public function getStaffBindUrl($assistant)
    {
        try {
            $result = $this->getSCRMSdk()->getStaffBindUrl($assistant['uuid']);
            $bindUrl = $result['url'];
        } catch (\Exception $e) {
            $bindUrl = '';
        }

        return $bindUrl;
    }

    public function getWechatOauthLoginUrl($user, $redirectUrl)
    {
        try {
            $result = $this->getSCRMSdk()->getWechatOauthLoginUrl($user['uuid'], '', $redirectUrl);
            $bindUrl = $result['url'];
        } catch (\Exception $e) {
            $bindUrl = '';
        }

        return $bindUrl;
    }

    public function uploadSCRMUserData($list)
    {
        if (empty($list)) {
            return;
        }

        $data = ['dynamicList' => $list, 'eventTime' => time()];

        return $this->getSCRMSdk()->uploadUserMessage($data);
    }

    /**
     * @return ScrmService
     */
    protected function getSCRMSdk()
    {
        $biz = $this->biz;

        return $biz['ESCloudSdk.scrm'];
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
