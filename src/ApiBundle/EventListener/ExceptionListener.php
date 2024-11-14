<?php

namespace ApiBundle\EventListener;

use ApiBundle\Api\Util\ExceptionUtil;
use ApiBundle\ApiBundle;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Topxia\Service\Common\ServiceKernel;

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
            $traceId = Uuid::uuid1()->getHex();
            $error['traceId'] = $traceId;
            $this->getLogger()->error("traceId:{$traceId}>>>".$error['message'], [$this->getRequestParameters($event->getRequest()), $exception->getMessage(), $exception->getTraceAsString()]);
            if (Response::HTTP_INTERNAL_SERVER_ERROR == $httpCode) {
                $error['message'] .= "#{$error['traceId']}";
            }
            $response = $this->container->get('api_response_viewer')->view(['error' => $error], $httpCode);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    private function getRequestParameters($request)
    {
        return [
            'body' => $request->request->all(),
            'query' => $request->query->all(),
        ];
    }

    private function isApiPath($request)
    {
        return false !== strpos($request->getPathInfo(), ApiBundle::API_PREFIX);
    }

    private function isDebug()
    {
        $env = $this->container->get('kernel')->getEnvironment();

        return 'dev' == $env || 'test' == $env;
    }

    private function getLogger()
    {
        $logger = new Logger('APIError');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/api-error.log', Logger::DEBUG));

        return $logger;
    }
}
