<?php


namespace ApiBundle\Api\Resource\MobileBindCheck;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class MobileBindCheck extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $checkType = $request->query->get('checkType', '');
        $identify = $request->query->get('identify', '');

        if (empty($identify)){
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if (empty($checkType)){
            $user = $this->getUserService()->getUserByLoginField($identify);
        }else{
            $type = $request->query->get('type', '');
            if (empty($type)){
                throw CommonException::ERROR_PARAMETER_MISSING();
            }
            $bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $identify);
            $user = $bind ? $this->getUserService()->getUser($bind['toId']) : [];
        }

        if (empty($user)){
            throw UserException::NOTFOUND_USER();
        }

        return [
            'isBindMobile' => empty($user['verifiedMobile']) ? false : true,
            'mobile_bind_mode' => $this->getSettingService()->get('auth.mobile_bind_mode', 'constraint')
        ];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}