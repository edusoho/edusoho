<?php

namespace MarketingMallBundle\Api\Resource\MallUser;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallUser extends BaseResource
{
    /**
     * @param ApiRequest $request
     * @return array
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter")
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['mobile', 'nickname'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $fields = ArrayToolkit::parts($fields, ['mobile', 'nickname', 'openId', 'avatar']);
        $user = $this->getUserService()->getUserByVerifiedMobile($fields['mobile']);
        if ($user) {
            return $user;
        }
        $fields['verifiedMobile'] = $fields['mobile'];
        $fields['type'] = 'marketing_mall';
        $user = $this->getUserService()->register($fields, ['mobile']);

        if (!empty($fields['avatar'])) {
            $fields['avatar'] = str_replace('\/', '/', $fields['avatar']);
            $this->getUserService()->changeAvatarFromImgUrl($user['id'], $fields['avatar']);
        }

        if ($fields['openId']) {
            $this->getUserService()->UserBindUpdate($fields['openId'], $user['id']);
        }

        $this->getLogService()->info('marketing_mall', 'register', "营销商城用户{$user['nickname']}通过手机注册成功", ['userId' => $user['id']]);

        return $user;
    }

    /**
     * @param ApiRequest $request
     * @param $identify
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     * @return null | array
     */
    public function get(ApiRequest $request, $identify)
    {
        $identifyType = $request->query->get('identifyType', 'id');

        $methods = [
            'id' => 'getUser',
            'email' => 'getUserByEmail',
            'mobile' => 'getUserByVerifiedMobile',
            'nickname' => 'getUserByNickname',
            'uuid' => 'getUserByUUID',
        ];
        if ('nickname' == $identifyType) {
            $identify = urldecode($identify);
        }
        if (empty($methods[$identifyType])) {
            return null;
        }
        $method = $methods[$identifyType];

        return $this->getUserService()->$method($identify);
    }

    /**
     * @param ApiRequest $request
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     * @return array
     */
    public function search(ApiRequest $request)
    {
        $ids = explode(',', $request->query->get('userIds'));
        $users = $this->getUserService()->findUsersByIds($ids);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($ids);
        foreach ($userProfiles as $userProfile) {
            $users[$userProfile['id']]['about'] = $userProfile['about'];
        }

        return array_values($users);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->service('System:LogService');
    }
}
