<?php

namespace AppBundle\Listener;

use AppBundle\Common\ExceptionPrintingToolkit;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
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
        $statusCode = $this->getStatusCode($exception);

        $request = $event->getRequest();
        if (!$request->isXmlHttpRequest()) {
            $this->setTargetPath($request);
            $exception = $this->convertException($exception);
            $statusCode = $this->getStatusCode($exception);
            $user = $this->getUser();
            if (Response::HTTP_FORBIDDEN === $statusCode && empty($user)) {
                $response = new RedirectResponse($this->container->get('router')->generate('login'));
                $event->setResponse($response);
            }
            $event->setException($exception);

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

        $error = array('name' => 'Error');

        if ($this->container->get('kernel')->isDebug()) {
            $error['message'] = $exception->getMessage();
            $error['trace'] = ExceptionPrintingToolkit::printTraceAsArray($exception);
        } else {
            $error['message'] = 'Request occurs an error';
        }

        if (403 === $statusCode) {
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

    protected function setTargetPath(Request $request)
    {
        // session isn't required when using HTTP basic authentication mechanism for example
        if ($request->hasSession() && $request->isMethodSafe(false) && !$request->isXmlHttpRequest()) {
            $request->getSession()->set('_target_path', $request->getUri());
        }
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

    private function convertException($exception)
    {
        if ($exception instanceof AccessDeniedException) {
            return new AccessDeniedHttpException($exception->getMessage(), $exception);
        }
        if ($exception instanceof NotFoundException) {
            return new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return $exception;
    }

    private function getStatusCode($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }
        $statusCode = $exception->getCode();
        if (in_array($statusCode, array_keys(Response::$statusTexts))) {
            return $statusCode;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
