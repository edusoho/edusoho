<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use VipPlugin\Biz\Vip\Service\VipService;
use ApiBundle\Api\Annotation\ResponseFilter;
use Biz\System\SettingException;
use Biz\Sensitive\SensitiveException;
use Biz\User\UserException;

class MeNickname extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Me\MeFilter", mode="simple")
     */
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
            throw SettingException::FORBIDDEN_NICKNAME_UPDATE();
        }

        if ($this->getSensitiveService()->scanText($nickname)) {
            throw SensitiveException::FORBIDDEN_WORDS();
        }

        list($result, $message) = $this->getAuthService()->checkUsername($nickname);

        if ('success' !== $result && $user['nickname'] != $nickname) {
            switch ($result) {
                case 'error_db':
                    throw UserException::UPDATE_NICKNAME_ERROR();
                    break;
                case 'error_mismatching':
                    throw UserException::NICKNAME_INVALID();
                    break;
                case 'error_duplicate':
                    throw UserException::NICKNAME_EXISTED();
                    break;
                default:
                    break;
            }
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
