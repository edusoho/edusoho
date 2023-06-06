<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\MobileMaskService;
use Biz\Util\Phpsec\Crypt\AES;
use Biz\Util\Phpsec\Crypt\Base;
use Ramsey\Uuid\Uuid;

class MobileMaskServiceImpl extends BaseService implements MobileMaskService
{
    protected $crypt;

    public function maskMobile($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }

    public function encryptMobile($mobile)
    {
        return base64_encode($this->getCrypt()->encrypt($mobile));
    }

    public function decryptMobile($encryptedMobile)
    {
        return $this->getCrypt()->decrypt(base64_decode($encryptedMobile));
    }

    protected function getCrypt()
    {
        if (empty($this->crypt)) {
            $this->crypt = new AES(Base::MODE_ECB);
            $this->crypt->setKey($this->getEncryptKey());
        }

        return $this->crypt;
    }

    protected function getEncryptKey()
    {
        $mobileEncryptKey = $this->getSettingService()->get('mobile_encrypt_key');
        if (empty($mobileEncryptKey)) {
            $mobileEncryptKey = Uuid::uuid4()->getHex();
            $this->getSettingService()->set('mobile_encrypt_key', $mobileEncryptKey);
        }

        return md5($this->getCurrentUser()->uuid.$mobileEncryptKey);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
