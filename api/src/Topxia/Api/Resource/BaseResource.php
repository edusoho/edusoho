<?php

namespace Topxia\Api\Resource;

use AppBundle\Util\CdnUrl;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Service\Common\ServiceKernel;

abstract class BaseResource
{
    private $logger;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return Biz
     */
    final protected function getBiz()
    {
        return $this->biz;
    }

    abstract public function filter($res);

    final protected function createService($service)
    {
        return $this->getBiz()->service($service);
    }

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

    protected function guessDeviceFromUserAgent($userAgent)
    {
        $userAgent = strtolower($userAgent);

        $ios = array('iphone', 'ipad', 'ipod');
        foreach ($ios as $keyword) {
            if (strpos($userAgent, $keyword) > -1) {
                return 'ios';
            }
        }

        if (strpos($userAgent, 'Android') > -1) {
            return 'android';
        }

        return 'unknown';
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
        $user = $this->destroyedNicknameFilter($user);

        $simple['id'] = $user['id'];
        $simple['nickname'] = $user['nickname'];
        $simple['title'] = $user['title'];
        $simple['roles'] = $user['roles'];
        $simple['avatar'] = $this->getFileUrl($user['smallAvatar']);
        $simple['uuid'] = $user['uuid'];

        return $simple;
    }

    protected function destroyedNicknameFilter($user)
    {
        $user['nickname'] = ($user['destroyed'] == 1) ? '帐号已注销' : $user['nickname'];

        return $user;
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

    public function filterHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);
        if (empty($matches)) {
            return $text;
        }

        $urls = array_unique($matches[1]);
        foreach ($urls as $url) {
            $text = str_replace($url, $this->getFileUrl($url, '', 'content'), $text);
        }

        return $text;
    }

    /**
     * @param $path
     * @param string $defaultKey
     * @param string $cdnType
     *
     * @return mixed|string
     */
    public function getFileUrl($path, $defaultKey = '', $cdnType = 'default')
    {
        if (empty($path)) {
            if (empty($defaultKey)) {
                return '';
            }

            $defaultSetting = $this->getSettingService()->get('default', array());
            if (('course.png' == $defaultKey && !empty($defaultSetting['defaultCoursePicture'])) || 'avatar.png' == $defaultKey && !empty($defaultSetting['defaultAvatar']) && empty($defaultSetting[$defaultKey])) {
                $path = $defaultSetting[$defaultKey];
            } else {
                return $this->getBaseUrl($cdnType).'/assets/img/default/'.$defaultKey;
            }
        }

        if (false !== strpos($path, $this->getHttpHost().'://')) {
            return $path;
        }
        if (false !== strpos($path, 'http://') || false !== strpos($path, 'https://')) {
            return $path;
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $files = 0 == strpos($path, '/') ? '/files' : '/files/';
        $path = $this->getBaseUrl($cdnType).$files."{$path}";

        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $path = $this->getBaseUrl()."/assets/{$path}";

        return $path;
    }

    protected function getHttpHost()
    {
        return $this->getSchema()."://{$_SERVER['HTTP_HOST']}";
    }

    protected function getSchema()
    {
        $https = empty($_SERVER['HTTPS']) ? '' : $_SERVER['HTTPS'];
        if (!empty($https) && 'off' !== strtolower($https)) {
            return 'https';
        }

        return 'http';
    }

    protected function isSsl()
    {
        return 'https' == $this->getSchema();
    }

    protected function getCdn($type = 'default')
    {
        $cdn = new CdnUrl();

        return $cdn->get($type);
    }

    protected function getBaseUrl($type = 'default')
    {
        $cdnUrl = $this->getCdn($type);
        if (!empty($cdnUrl)) {
            return $this->getSchema().':'.$cdnUrl;
        }

        return $this->getHttpHost();
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
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function addError($logName, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->error($message);
    }

    protected function addDebug($logName, $message)
    {
        if (!$this->isDebug()) {
            return;
        }
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->debug($message);
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function isDebug()
    {
        return 'dev' == $this->getServiceKernel()->getEnvironment();
    }

    protected function getLogger($name)
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/service.log', Logger::DEBUG));

        return $this->logger;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \Biz\S2B2C\Service\S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
