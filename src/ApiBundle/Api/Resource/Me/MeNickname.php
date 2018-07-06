<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use VipPlugin\Biz\Vip\Service\VipService;

class MeNickname extends AbstractResource
{
    public function update(ApiRequest $request, $nickname)
    {
        $this->checkNickname($nickname);
        $this->getAuthService()->changeNickname($this->getCurrentUser()->getId(), $nickname);
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
        $this->appendUser($user);

        return $user;
    }

    protected function checkNickname($nickname)
    {
        $user = $this->getCurrentUser();
        $userSetting = $this->getSettingService()->get('user_partner');
        if (empty($userSetting['nickname_enabled'])) {
            //不允许修改异常
        }

        if ($this->getSensitiveService()->scanText($nickname)) {
            //敏感词昵称不允许修改
        }

        list($result, $message) = $this->getAuthService()->checkUsername($nickname);

        if ('success' !== $result && $user['nickname'] != $nickname) {
            //根据不同code返回异常
        }
    }

    protected function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->service('VipPlugin:Vip:VipService')->getMemberByUserId($user['id']);
            $level = $this->service('VipPlugin:Vip:LevelService')->getLevel($vip['levelId']);
            if ($vip) {
                $user['vip'] = array(
                    'levelId' => $vip['levelId'],
                    'vipName' => $level['name'],
                    'deadline' => date('c', $vip['deadline']),
                    'seq' => $level['seq'],
                );
            } else {
                $user['vip'] = null;
            }
        }

        return $user;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    protected function getSensitiveService()
    {
        return $this->service('Sensitive:SensitiveService');
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    protected function getAuthService()
    {
        return $this->service('User:AuthService');
    }
}
