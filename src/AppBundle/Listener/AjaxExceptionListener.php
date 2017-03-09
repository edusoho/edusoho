<?php

namespace AppBundle\Listener;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class AjaxExceptionListener
{
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $problem = $this->container->get('Topxia.RepairProblem', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $exception = $event->getException();
        $request = $event->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return;
        }

        if ($problem && !empty($problem['content'])) {
            ob_start();
            eval($problem['content']);
            $result = ob_get_contents();
            ob_end_clean();
            $event->setResponse(new JsonResponse(array('result' => $result)));

            return;
        }

        $statusCode = $exception->getCode();

        if (!array_key_exists($statusCode, Response::$statusTexts)) {
            $statusCode = 500;
        }

        $error = array('name' => 'Error', 'message' => $exception->getMessage());
        if (!$this->container->get('kernel')->isDebug()) {
            ServiceKernel::instance()->createService('System:LogService')->error('ajax', 'exception', $exception->getMessage());
        } else {
            $this->getLogger()->error($exception->__toString());
        }

        if ($statusCode == 403) {
            $user = $this->getUser($event);
            if ($user) {
                $error = array('name' => 'AccessDenied', 'message' => $this->getServiceKernel()->trans('访问被拒绝！'));
            } else {
                $error = array('name' => 'Unlogin', 'message' => $this->getServiceKernel()->trans('当前操作，需要登录！'));
            }
        }

        $response = new JsonResponse(array('error' => $error), $statusCode);
        $event->setResponse($response);
    }

    public function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    protected function getLogger()
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger('AjaxExceptionListener');
        $this->logger->pushHandler(new StreamHandler($this->getServiceKernel()->getParameter('kernel.logs_dir').'/dev.log', Logger::DEBUG));

        return $this->logger;
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
