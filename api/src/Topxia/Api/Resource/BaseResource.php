<?php

namespace Topxia\Api\Resource;

use Topxia\Service\Common\ServiceKernel;

abstract class BaseResource
{
    abstract public function filter($res);

    protected function callFilter($name, $res)
    {
        global $app;
        return $app["res.{$name}"]->filter($res);
    }

    protected function multicallFilter($name, $res)
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
        global $app;
        return $app["res.{$name}"]->simplify($res);
    }

    protected function multicallSimplify($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callSimplify($name, $one);
        }
        return $res;
    }

    /**
     * 检查每个API必需参数的完整性
     */
    protected function checkRequiredFields($requiredFields, $requestData)
    {
        $requestFields = array_keys($requestData);
        foreach ($requiredFields as $field) {
            if (!in_array($field, $requestFields)) {
                throw new \Exception("缺少必需的请求参数{$field}");
            }
        }

        return $requestData;
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
            return array('resources' => $resources, 'total' => $total ? : 0);
        }
    }

    protected function simpleUsers($users)
    {
        $newArray = array();
        foreach ($users as $key => $user) {
            $newArray[$key] = $this->simpleUser($user);
        }

        return $newArray;
    }

    protected function simpleUser($user)
    {
        $simple = array();

        $simple['id'] = $user['id'];
        $simple['nickname'] = $user['nickname'];
        $simple['title'] = $user['title'];
        $simple['roles'] = $user['roles'];
        $simple['avatar'] = $this->getFileUrl($user['smallAvatar']);

        return $simple;
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
        if (strpos($path, "http://") !== false) {
            return $path;
        }
        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = $this->getHttpHost()."/files/{$path}";

        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $path = $this->getHttpHost()."/assets/{$path}";
        return $path;
    }

    protected function getHttpHost()
    {
        return "http://{$_SERVER['HTTP_HOST']}";
    }

    protected function generateUrl($route, $parameters = array())
    {
        global $app;
        return $app['url_generator']->generate($route, $parameters);
    }

    protected function render($templatePath, $args)
    {
        global $app;
        return $app['twig']->render($templatePath, $args);
    }

    protected function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
