<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Common\DeviceToolkit;

class KernelH5RequestListener
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

        if ('GET' !== $request->getMethod()) {
            return;
        }

        $pathInfo = $request->getPathInfo();
        $route = $this->container
            ->get('router')
            ->getMatcher()
            ->match($pathInfo);

        $h5 = empty($route['_h5']) ? array() : $route['_h5'];
        if (empty($h5)) {
            return;
        }

        $wapSetting = $this->getSettingService()->get('wap');
        if (empty($wapSetting['enabled'])) {
            return;
        }

        if (!DeviceToolkit::isMobileClient()) {
            return;
        }

        $params = $request->query->all();
        $query = http_build_query($params);
        $url = empty($query) ? '/h5/index.html#'.$pathInfo : '/h5/index.html#'.$pathInfo.'?'.$query;
        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function trans($id, array $parameters = array())
    {
        return $this->container->get('translator')->trans($id, $parameters);
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
