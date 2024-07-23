<?php

namespace MarketingMallBundle\Api\Resource\MallUser;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use Biz\User\Service\UserService;
use Biz\WeChat\Service\WeChatService;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallUserWechatNickname extends BaseResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request, $id)
    {
        $params = $request->request->all();
        $bind = $this->getUserService()->getUserBindByTypeAndFromId('weixinmob', $params['unionId']);
        $this->getWeChatService()->freshOfficialWeChatUserWhenLogin(['id' => $id], $bind, ['openid' => $params['openId'], 'username' => $params['nickname']]);

        return ['success' => true];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->service('WeChat:WeChatService');
    }
}
