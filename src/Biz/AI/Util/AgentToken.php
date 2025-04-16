<?php

namespace Biz\AI\Util;

use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use Topxia\Service\Common\ServiceKernel;

class AgentToken
{
    private $accessKey = '';

    private $secretKey = '';

    public function __construct()
    {
        $storage = $this->getSettingService()->get('storage', []);
        $this->accessKey = empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'];
        $this->secretKey = empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'];
    }

    public function make()
    {
        $payload = [
            'iss' => 'AI_AGENT',
            'sub' => $this->getCurrentUser()->getId(),
            'exp' => time() + 3600 * 24 * 7,
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256', $this->accessKey);
    }

    private function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getServiceKernel()->getBiz()->service('System:SettingService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
