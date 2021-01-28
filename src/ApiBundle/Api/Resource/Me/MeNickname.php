<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ResponseFilter;
use Biz\System\SettingException;
use Biz\Sensitive\SensitiveException;
use Biz\User\UserException;

class MeNickname extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Me\MeFilter", mode="simple")
     */
    //portal暂时无用，仅用来匹配patch method
    public function update(ApiRequest $request, $portal)
    {
        $nickname = $request->request->get('nickname');
        $this->checkNickname($nickname);
        $this->getAuthService()->changeNickname($this->getCurrentUser()->getId(), $nickname);

        return $this->getUserService()->getUser($this->getCurrentUser()->getId());
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
