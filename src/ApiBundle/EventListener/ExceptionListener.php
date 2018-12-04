<?php

namespace ApiBundle\EventListener;

use ApiBundle\Api\Util\ExceptionUtil;
use ApiBundle\ApiBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->isApiPath($event->getRequest())) {
            $exception = $event->getException();

            list($error, $httpCode) = ExceptionUtil::getErrorAndHttpCodeFromException($exception, $this->isDebug());

            $error['message'] = $this->container->get('translator')->trans($error['message']);

            $response = $this->container->get('api_response_viewer')->view(array('error' => $error), $httpCode);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    private function isApiPath($request)
    {
        return strpos($request->getPathInfo(), ApiBundle::API_PREFIX) !== false;
    }

    private function isDebug()
    {
        $env = $this->container->get('kernel')->getEnvironment();

        return $env == 'dev' || $env == 'test';
    }
}
