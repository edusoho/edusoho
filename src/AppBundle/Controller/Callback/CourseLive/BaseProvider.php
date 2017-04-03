<?php

namespace AppBundle\Controller\Callback\CourseLive;

use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\Context\BizAware;

abstract class BaseProvider extends BizAware
{
    public function get(Request $request)
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

    public function getFileUrl($path, $default = '')
    {
        if (empty($path)) {
            if (empty($default)) {
                return '';
            }
            $path = $this->getHttpHost().'/assets/img/default/'.$default;
            return $path;
        };
        
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

    public function getBiz()
    {
        return $this->biz;
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
