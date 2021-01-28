<?php

namespace ApiBundle\Security\Authentication;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Weblib\Auth\KeyProvider;
use Codeages\Weblib\Auth\AccessKey;

class WebLibAuthenticationKeyProvider implements KeyProvider
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function get($id)
    {
        $storage = $this->getSettingService()->get('storage', array());
        $accessKey = $storage['cloud_access_key'];
        $secretKey = $storage['cloud_secret_key'];

        return new AccessKey($accessKey, $secretKey, 'active');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
