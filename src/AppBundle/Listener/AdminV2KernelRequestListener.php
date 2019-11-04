<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminV2KernelRequestListener
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
        $settingService = $this->getSettingService();

        $adminV2Setting = $settingService->get('backstage', array('is_v2' => false));
        preg_match('/^\/admin\/v2/', $request->getPathInfo(), $matches);

        if (!empty($adminV2Setting) && $adminV2Setting['is_v2'] && empty($matches)) {
            $goto = $this->container->get('router')->generate('admin_v2');
            $response = new RedirectResponse($goto, '302');
            $event->setResponse($response);
        }

        if ((empty($adminV2Setting) || !$adminV2Setting['is_v2']) && !empty($matches)) {
            $goto = $this->container->get('router')->generate('admin');
            $response = new RedirectResponse($goto, '302');
            $event->setResponse($response);
        }
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
