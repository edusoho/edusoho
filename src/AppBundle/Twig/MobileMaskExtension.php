<?php

namespace AppBundle\Twig;

use Biz\Util\Phpsec\Crypt\AES;
use Biz\Util\Phpsec\Crypt\Base;

class MobileMaskExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('mobile_mask', [$this, 'mask']),
            new \Twig_SimpleFunction('mobile_encrypt', [$this, 'encrypt']),
        ];
    }

    public function mask($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }

    public function encrypt($mobile)
    {
        $encryptor = new AES(Base::MODE_ECB);
        $encryptor->setKey('mobile_encrypt_key');

        return $encryptor->encrypt($mobile);
    }
}
