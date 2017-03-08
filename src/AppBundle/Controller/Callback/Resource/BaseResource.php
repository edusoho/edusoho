<?php

namespace AppBundle\Controller\Callback\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseResource implements ResourceInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(Request $request)
    {
        return array();
    }

    public function post(Request $request)
    {
        return array();
    }

    protected function error($code, $message)
    {
        return array('error' => array(
            'code' => $code,
            'message' => $message,
        ));
    }

    protected function wrap($resources, $total)
    {
        if (is_array($total)) {
            return array('resources' => $resources, 'next' => $total);
        } else {
            return array('resources' => $resources, 'total' => $total ?: 0);
        }
    }

    public function filter($res)
    {
        return $res;
    }

    protected function callFilter($name, $res)
    {
        return $this->container->get('callback.resource_factory')->create($name)->filter($res);
    }

    protected function multicallFilter($name, array $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
        }

        return $res;
    }

    protected function simplify($res)
    {
        return $res;
    }

    protected function callSimplify($name, $res)
    {
        return $this->container->get('callback.resource_factory')->create($name)->simplify($res);
    }

    protected function multicallSimplify($name, array $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callSimplify($name, $one);
        }

        return $res;
    }

    protected function callBuild($name, array $res)
    {
        return $this->container->get('callback.resource_factory')->create($name)->build($res);
    }

    protected function singlecallBuild($name, $res)
    {
        return array_shift($this->callBuild($name, array($res)));
    }

    protected function checkRequiredFields($requestData, $requiredFields)
    {
        $requestFields = array_keys($requestData);
        foreach ($requiredFields as $field) {
            if (!in_array($field, $requestFields)) {
                throw new \InvalidArgumentException(sprintf('missing param: %s', $field));
            }
        }

        return $requestData;
    }

    protected function nextCursorPaging($currentCursor, $currentStart, $currentLimit, $currentRows)
    {
        $end = end($currentRows);
        if (empty($end)) {
            return array(
                'cursor' => $currentCursor + 1,
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => true,
            );
        }

        if (count($currentRows) < $currentLimit) {
            return array(
                'cursor' => $end['updatedTime'] + 1,
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => true,
            );
        }

        if ($end['updatedTime'] != $currentCursor) {
            $next = array(
                'cursor' => $end['updatedTime'],
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => false,
            );
        } else {
            $next = array(
                'cursor' => $currentCursor,
                'start' => $currentStart + $currentLimit,
                'limit' => $currentLimit,
                'eof' => false,
            );
        }

        return $next;
    }

    public function getFileUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        if (strpos($path, $this->getHttpHost().'://') !== false) {
            return $path;
        }
        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = $this->getHttpHost()."/files/{$path}";

        return $path;
    }

    protected function getHttpHost()
    {
        $schema = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) ? 'https' : 'http';

        return $schema."://{$_SERVER['HTTP_HOST']}";
    }

    protected function generateUrl($route, $parameters = array())
    {
        return $this->container->get('router')->generate($route, $parameters);
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return isset($biz['user']) ? $biz['user'] : array();
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
