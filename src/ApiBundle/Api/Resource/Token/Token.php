<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Pay\Service\AccountService;

class Token extends AbstractResource
{
    const MOBILE_MODULE = 'mobile';

    const TOKEN_TYPE = 'mobile_login';

    public function add(ApiRequest $request)
    {
        $type = $request->request->get('type');
        $client = $request->request->get('client', '');
        $user = $this->getCurrentUser()->toArray();

        $expiredTime = time() + 3600 * 24 * 30;
        $token = $this->getUserService()->makeToken(self::TOKEN_TYPE, $user['id'],$expiredTime , ['client' => $client]);
        $refreshToken = $this->getUserService()->makeToken("refreshToken", $user['id'], time() + 3600 * 24 * 180, ['client' => $client]);

        $this->appendUser($user);
        $this->getUserService()->markLoginInfo($type);

        if ('app' == $client) {
            $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
            $this->deleteInvalidToken($user['id'], $token, $refreshToken);
        }

        return [
            'token' => $token,
            'tokenExpire' => $expiredTime,
            'refreshToken' => $refreshToken,
            'user' => $user,
        ];
    }

    public function remove(ApiRequest $request, $token)
    {
        $user = $this->getCurrentUser()->toArray();

        $device = $this->getPushDeviceService()->getPushDeviceByUserId($user['id']);
        if (!empty($device)) {
            $device = $this->getPushDeviceService()->updatePushDevice($device['id'], ['userId' => 0]);
            $this->getPushDeviceService()->getPushSdk()->setDeviceActive($device['regId'], 0);
        }

        $this->getLogService()->info(self::MOBILE_MODULE, 'user_logout', '用户退出', ['userToken' => $user]);

        $this->getUserService()->deleteToken(self::TOKEN_TYPE, $user['loginToken']);

        return ['success' => true];
    }

    private function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->service('VipPlugin:Vip:VipService')->getMemberByUserId($user['id']);
            if ($vip) {
                $level = $this->service('VipPlugin:Vip:LevelService')->getLevel($vip['levelId']);
                $user['vip'] = [
                    'levelId' => $vip['levelId'],
                    'vipName' => $level['name'],
                    'deadline' => date('c', $vip['deadline']),
                    'seq' => $level['seq'],
                ];
            } else {
                $user['vip'] = null;
            }
        }

        $storageSetting = $this->getSettingService()->get('storage');
        if (isset($storageSetting['video_fingerprint_content'])) {
            $fingerPrint = $this->getWebExtension()->getFingerprint();
            $user['fingerPrintSetting']['video_fingerprint_content'] = substr($fingerPrint, strpos($fingerPrint, '>') + 1, strrpos($fingerPrint, '<') - strlen($fingerPrint));
        }

        $user['havePayPassword'] = $this->getAccountService()->isPayPasswordSetted($user['id']) ? 1 : -1;

        return $user;
    }

    protected function deleteInvalidToken($userId, $token, $refreshToken)
    {
        $delTokens = $this->getTokenService()->findTokensByUserIdAndType($userId, self::TOKEN_TYPE);
        $delRefreshTokens = $this->getTokenService()->findTokensByUserIdAndType($userId, "refreshToken");
        $delTokens = array_merge($delTokens, $delRefreshTokens);
        foreach ($delTokens as $delToken) {
            if ($delToken['token'] != $token && $delToken['token'] != $refreshToken) {
                $this->getTokenService()->destoryToken($delToken['token']);
            }
        }
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
