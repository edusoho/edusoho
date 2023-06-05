<?php

namespace AppBundle\Twig;

use Biz\User\Service\MobileMaskService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MobileMaskExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
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
        return $this->getMobileMaskService()->maskMobile($mobile);
    }

    public function encrypt($mobile)
    {
        return $this->getMobileMaskService()->encryptMobile($mobile);
    }

    /**
     * @return MobileMaskService
     */
    private function getMobileMaskService()
    {
        return $this->biz->service('User:MobileMaskService');
    }
}
