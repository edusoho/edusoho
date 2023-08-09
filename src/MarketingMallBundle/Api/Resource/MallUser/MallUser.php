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
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['unionId', 'nickname'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $fields = ArrayToolkit::parts($fields, ['nickname', 'openId', 'avatar', 'unionId']);
        $userBind = $this->getUserService()->getUserBindByTypeAndFromId('weixin', $fields['unionId']);
        if (!empty($userBind) && $userBind['toId'] != 0) {
            $user = $this->getUserService()->getUser($userBind['toId']);
            if ($user) {
                return $user;
            }
        }

        if (!$this->getUserService()->isNicknameAvaliable($fields['nickname'])) {
            $fields['nickname'] = $this->generateNickname($fields['nickname']);
        }

        $fields['email'] = $this->getUserService()->generateEmail($fields);
        $fields['type'] = 'marketing_mall';
        $user = $this->getUserService()->register($fields);

        if (!empty($fields['avatar'])) {
            $fields['avatar'] = str_replace('\/', '/', $fields['avatar']);
            foreach ($this->getValidAvatarHosts() as $validAvatarHost) {
                if (0 === strpos($fields['avatar'], $validAvatarHost)) {
                    $this->getUserService()->changeAvatarFromImgUrl($user['id'], $fields['avatar']);
                }
            }
        }

        if (!empty($fields['unionId'])) {
            $this->getUserService()->bindUser('weixin', $fields['unionId'], $user['id'], ['openid' => $fields['openId']]);
        }

        $this->getLogService()->info('marketing_mall', 'register', "营销商城用户{$user['nickname']}通过邮箱注册成功", ['userId' => $user['id']]);

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

    private function getValidAvatarHosts()
    {
        return [
            'https://thirdwx.qlogo.cn/',
        ];
    }

    private function generateNickname($rawNickname)
    {
        $nickname = $rawNickname . substr($this->getRandomChar(), 0, 4);
        if ($this->getUserService()->isNicknameAvaliable($nickname)) {
            return $nickname;
        }

        return $this->generateNickname($rawNickname);
    }

    private function getRandomChar()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
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
