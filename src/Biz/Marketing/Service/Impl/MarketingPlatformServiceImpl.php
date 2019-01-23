<?php

namespace Biz\Marketing\Service\Impl;

use Biz\BaseService;
use Biz\Marketing\Service\MarketingPlatformService;
use Biz\Marketing\MarketingAPIFactory;
use Biz\User\UserException;

class MarketingPlatformServiceImpl extends BaseService implements MarketingPlatformService
{
    public function simpleLogin($userId)
    {
        $client = $this->getMarketingAPIClient();
        $user = $this->getUserService()->getUser($userId);
        if (empty($user) || !$this->getUserService()->hasAdminRoles($userId)) {
            throw UserException::PERMISSION_DENIED();
        }
        $client->post(
            '/simple_login',
            array(
                'user_id' => $user['id'],
                'user_name' => $user['nickname'],
            )
        );
    }

    public function getActivity($activityId)
    {
        $systemUser = $this->getUserService()->getUserByType('system');
        $this->simpleLogin($systemUser['id']);
        $client = $this->getMarketingAPIClient();

        return $client->get(
            '/activities/'.$activityId,
            array('from' => 'edusoho'),
            array('MERCHANT-USER-ID: '.$systemUser['id'])
        );
    }

    protected function getMarketingAPIClient()
    {
        return MarketingAPIFactory::create();
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
