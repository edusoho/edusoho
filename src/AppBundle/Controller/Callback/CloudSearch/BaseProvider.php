<?php

namespace AppBundle\Controller\Callback\CloudSearch;

use Biz\Common\CommonException;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\Context\BizAware;

abstract class BaseProvider extends BizAware
{
    public function get(Request $request)
    {
        return array();
    }

    public function checkToken($token)
    {
        $this->decodeKeysign($token);
    }

    protected function decodeKeysign($token)
    {
        $token = explode(':', $token);

        if (count($token) != 3) {
            throw new \RuntimeException('API Token格式不正确！');
        }

        list($accessKey, $policy, $sign) = $token;

        if (empty($accessKey) || empty($policy) || empty($sign)) {
            throw new \RuntimeException('API Token不正确！');
        }

        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            throw new \RuntimeException('系统尚未配置AccessKey/SecretKey');
        }

        if ($accessKey != $settings['cloud_access_key']) {
            throw new \RuntimeException('AccessKey不正确！');
        }

        $expectedSign = $this->encodeBase64(hash_hmac('sha1', $policy, $settings['cloud_secret_key'], true));

        if ($sign != $expectedSign) {
            throw new \RuntimeException('API Token 签名不正确！');
        }

        $policy = json_decode($this->decodeBase64($policy), true);

        if (empty($policy)) {
            throw new \RuntimeException('API Token 解析失败！');
        }

        if (time() > $policy['deadline']) {
            throw new \RuntimeException(sprintf('API Token 已过期！(%s)', date('Y-m-d H:i:s')));
        }

        return $policy;
    }

    public function encodeKeysign($request, $role = 'guest', $lifetime = 600)
    {
        $settings = $this->getSettingService()->get('storage', array());

        $policy = array(
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'role' => $role,
            'deadline' => time() + $lifetime,
        );

        $encoded = $this->encodeBase64(json_encode($policy));

        $sign = hash_hmac('sha1', $encoded, $settings['cloud_secret_key'], true);

        return $settings['cloud_access_key'].':'.$encoded.':'.$this->encodeBase64($sign);
    }

    protected function encodeBase64($string)
    {
        $find = array('+', '/');
        $replace = array('-', '_');

        return str_replace($find, $replace, base64_encode($string));
    }

    protected function decodeBase64($string)
    {
        $find = array('-', '_');
        $replace = array('+', '/');

        return base64_decode(str_replace($find, $replace, $string));
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
        return $this->biz['callback.cloud_search_processor']->getProvider($name)->filter($res);
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
        return $this->biz['callback.cloud_search_processor']->getProvider($name)->simplify($res);
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
        return $this->biz['callback.cloud_search_processor']->getProvider($name)->build($res);
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
                throw CommonException::ERROR_PARAMETER_MISSING();
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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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
