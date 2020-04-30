<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use Codeages\Biz\Pay\Service\AccountService;
use Biz\System\Service\LogService;

class Token extends AbstractResource
{
    const MOBILE_MODULE = 'mobile';

    const TOKEN_TYPE = 'mobile_login';

    public function add(ApiRequest $request)
    {
        $type = $request->request->get('type');
        $client = $request->request->get('client', '');
        $user = $this->getCurrentUser()->toArray();

        $token = $this->getUserService()->makeToken(self::TOKEN_TYPE, $user['id'], time() + 3600 * 24 * 30, ['client' => $client]);

        $this->appendUser($user);
        $this->getUserService()->markLoginInfo($type);

        if ('app' == $client) {
            $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);

            $delTokens = $this->getTokenService()->findTokensByUserIdAndType($user['id'], self::TOKEN_TYPE);

            foreach ($delTokens as $delToken) {
                if ($delToken['token'] != $token) {
                    $this->getTokenService()->destoryToken($delToken['token']);
                }
            }
        }

        return array(
            'token' => $token,
            'user' => $user,
        );
    }

    public function remove(ApiRequest $request, $token)
    {
        $user = $this->getCurrentUser()->toArray();

        $device = $this->getPushDeviceService()->getPushDeviceByUserId($user['id']);
        if (!empty($device)) {
            $device = $this->getPushDeviceService()->updatePushDevice($device['id'], array('userId' => 0));
            $this->getPushDeviceService()->getPushSdk()->setDeviceActive($device['regId'], 0);
        }

        $this->getLogService()->info(self::MOBILE_MODULE, 'user_logout', '用户退出', array('userToken' => $user));

        $this->getUserService()->deleteToken(self::TOKEN_TYPE, $user['loginToken']);

        return array('success' => true);
    }

    private function appendUser(&$user)
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

        $user['havePayPassword'] = $this->getAccountService()->isPayPasswordSetted($user['id']) ? 1 : -1;

        return $user;
    }

    protected function getBatchNotificationService()
    {
        return $this->service('User:BatchNotificationService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return \Biz\PushDevice\Service\Impl\PushDeviceServiceImpl
     */
    protected function getPushDeviceService()
    {
        return $this->service('PushDevice:PushDeviceService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->service('Pay:AccountService');
    }
}
