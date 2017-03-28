<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Pimple\Container;

abstract class Resource implements ResourceInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    abstract public function auth(Request $request);

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

    protected function filterHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);
        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, $this->getFileUrl($url), $text);
        }

        return $text;
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
        $path = $this->getHttpHost().'/files/'.ltrim($path, '/');

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

    public function getBiz()
    {
        return $this->container->get('biz');
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return isset($biz['user']) ? $biz['user'] : array();
    }

    protected function createService($alias)
    {
        $biz = $this->getBiz();

        return $biz->service($alias);
    }
}
