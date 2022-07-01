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
        $fields = ArrayToolkit::parts($fields, ['mobile', 'nickname']);
        $user = $this->getUserService()->getUserByVerifiedMobile($fields['mobile']);
        if ($user) {
            return $user;
        }
        $fields['verifiedMobile'] = $fields['mobile'];
        $user = $this->getUserService()->register($fields, ['mobile']);
        $this->getLogService()->info('marketing_mall', 'register', "营销商城用户{$user['nickname']}通过手机注册成功", ['userId' => $user['id']]);

        return $user;
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
