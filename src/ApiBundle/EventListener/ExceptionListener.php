<?php

namespace ApiBundle\EventListener;

use ApiBundle\Api\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\FlattenException;

class ExceptionListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $error = array();
        $error['message'] = $exception->getMessage();
        if ($exception instanceof ApiException) {
            $error['code'] = $exception->getCode();
            $error['type'] = $exception->getType();
            $httpCode = $exception->getHttpCode();
        } else{
            $error['code'] = ApiException::CODE;
            $error['type'] = ApiException::TYPE;
            $httpCode = ApiException::HTTP_CODE;
        }

        if ($this->isDebug()) {

            if (!$exception instanceof FlattenException) {
                $exception = FlattenException::create($exception);
            }

            $error['previous'] = array();

            $flags = PHP_VERSION_ID >= 50400 ? ENT_QUOTES | ENT_SUBSTITUTE : ENT_QUOTES;

            $count = count($exception->getAllPrevious());
            $total = $count + 1;
            foreach ($exception->toArray() as $position => $e) {
                $previous = array();

                $ind = $count - $position + 1;

                $previous['message'] = "{$ind}/{$total} {$e['class']}: {$e['message']}";
                $previous['trace'] = array();

                foreach ($e['trace'] as $position => $trace) {
                    $content = sprintf('%s. ', $position+1);
                    if ($trace['function']) {
                        $content .= sprintf('at %s%s%s(%s)', $trace['class'], $trace['type'], $trace['function'], '...args...');
                    }
                    if (isset($trace['file']) && isset($trace['line'])) {
                        $content .= sprintf(' in %s line %d', htmlspecialchars($trace['file'], $flags, 'UTF-8'), $trace['line']);
                    }

                    $previous['trace'][] = $content;
                }

                $error['previous'][] = $previous;
            }
        }

        $response = new JsonResponse(array('error' => $error), $httpCode);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    private function isDebug()
    {
        $env = $this->container->get( 'kernel' )->getEnvironment();
        return $env == 'dev';
    }
}
