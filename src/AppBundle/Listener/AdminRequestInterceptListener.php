<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminRequestInterceptListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }
        $path = $request->getPathInfo();

        if (!$this->isAdminPath($path)) {
            return true;
        }

        if (!$this->isPathMatchSetting($path)) {
            $routing = $this->isAdminV2Setting() ? 'admin_v2' : 'admin';
            $url = $this->container->get('router')->generate($routing);
            $response = new RedirectResponse($url, '302');
            $event->setResponse($response);
        }

        return true;
    }

    protected function isPathMatchSetting($path)
    {
        return $this->isAdminV2Path($path) === $this->isAdminV2Setting();
    }

    protected function isAdminV2Setting()
    {
        $adminVersionSetting = $this->getSettingService()->get('backstage', array('is_v2' => 0));

        return !empty($adminVersionSetting['is_v2']);
    }

    protected function isAdminV2Path($path)
    {
        return 0 === strpos($path, '/admin/v2/');
    }

    protected function isAdminPath($path)
    {
        return 0 === strpos($path, '/admin/');
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
