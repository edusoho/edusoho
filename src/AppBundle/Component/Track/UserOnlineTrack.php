<?php

namespace AppBundle\Component\Track;

use AppBundle\Component\DeviceDetector\DeviceDetectorAdapter;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserOnlineTrack
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Codeages\Biz\Framework\Context\Biz
     */
    private $biz;

    /**
     * @var \AppBundle\Component\DeviceDetector\DeviceDetectorAdapter
     */
    private $deviceDetector;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
        $this->deviceDetector = new DeviceDetectorAdapter($this->getUserAgent());
    }

    /**
     * @todo api 和 web 的sessionId 应该统一
     *
     * @param $sessionId
     */
    public function track($sessionId)
    {
        // 过滤掉爬虫
        if ($this->deviceDetector->isBot()) {
            return;
        }

        $user = $this->biz['user'];
        $online = array(
            'sess_id' => $sessionId,
            'user_id' => $user['id'],
            'ip' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent(),
            'source' => $this->isFromApp() ? 'App' : 'Web',
        );

        $this->getUserActiveService()->saveOnline($online);
    }

    private function isFromApp()
    {
        $pathInfo = $this->getRequest()->getPathInfo();

        return 0 === strpos($pathInfo, '/api');
    }

    private function getRequest()
    {
        return $this->container->get('request_stack')->getMasterRequest();
    }

    private function getClientIp()
    {
        return $this->getRequest()->getClientIp();
    }

    private function getUserAgent()
    {
        return $this->getRequest()->headers->get('User-Agent');
    }

    /**
     * @return \Biz\User\Service\UserActiveService
     */
    private function getUserActiveService()
    {
        return $this->biz->service('User:UserActiveService');
    }
}
