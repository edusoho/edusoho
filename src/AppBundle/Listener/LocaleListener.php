<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LocaleListener extends AbstractSecurityDisabledListener implements EventSubscriberInterface
{
    private $defaultLocale;

    private $container;

    public function __construct($container, $defaultLocale)
    {
        $this->container = $container;
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasPreviousSession() || $this->isSecurityDisabledRequest($this->container, $request)) {
            return;
        }

        $settingService = $this->getSettingService();
        $developer = $settingService->get('developer');
        if (isset($developer['default_locale'])) {
            $this->defaultLocale = $developer['default_locale'];
        }

        $locale = $request->getSession()->get('_locale', $request->cookies->get('_last_logout_locale') ?: $this->defaultLocale);
        $request->setLocale($locale);
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
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
