<?php

namespace Biz\System\Service\Impl;

use Biz\AppLoggerConstant;
use Biz\BaseService;
use Biz\LoggerConstantInterface;
use Biz\System\Dao\LogDao;
use Biz\User\Service\UserService;
use Biz\System\Service\LogService;

class LogServiceImpl extends BaseService implements LogService
{
    public function info($module, $action, $message, array $data = null)
    {
        return $this->addLog('info', $module, $action, $message, $data);
    }

    public function warning($module, $action, $message, array $data = null)
    {
        return $this->addLog('warning', $module, $action, $message, $data);
    }

    public function error($module, $action, $message, array $data = null)
    {
        return $this->addLog('error', $module, $action, $message, $data);
    }

    public function searchLogs($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        if (!is_array($sort)) {
            switch ($sort) {
                case 'created':
                    $sort = array('id' => 'DESC');
                    break;
                case 'createdByAsc':
                    $sort = array('id' => 'ASC');
                    break;
                default:
                    throw $this->createServiceException('参数sort不正确。');
                    break;
            }
        }

        $logs = $this->getLogDao()->search($conditions, $sort, $start, $limit);

        foreach ($logs as &$log) {
            $log['data'] = empty($log['data']) ? array() : json_decode($log['data'], true);
            unset($log);
        }

        return $logs;
    }

    public function searchLogCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogDao()->count($conditions);
    }

    protected function addLog($level, $module, $action, $message, array $data = null)
    {
        $user = $this->getCurrentUser();

        return $this->getLogDao()->create(
            array(
                'module' => $module,
                'action' => $action,
                'message' => $message,
                'data' => empty($data) ? '' : json_encode($data),
                'userId' => $user['id'],
                'ip' => $user['currentIp'],
                'browser' => $this->getBrowse(),
                'operatingSystem' => $this->getOperatingSystem(),
                'device' => $this->getDevice(),
                'createdTime' => time(),
                'level' => $level,
            )
        );
    }

    public function analysisLoginNumByTime($startTime, $endTime)
    {
        return $this->getLogDao()->analysisLoginNumByTime($startTime, $endTime);
    }

    public function analysisLoginDataByTime($startTime, $endTime)
    {
        return $this->getLogDao()->analysisLoginDataByTime($startTime, $endTime);
    }

    public function getModules()
    {
        $loggerConstantList = $this->getLoggerConstantList();
        $modules = array();
        foreach ($loggerConstantList as $loggerConstant) {
            $modules = array_merge($modules, $loggerConstant->getModules());
        }

        return $modules;
    }

    public function getActionsByModule($module)
    {
        $loggerConstantList = $this->getLoggerConstantList();
        $actions = array();
        foreach ($loggerConstantList as $loggerConstant) {
            $actions = array_merge($actions, $loggerConstant->getActions());
        }

        if (isset($actions[$module])) {
            return $actions[$module];
        } else {
            return array();
        }
    }

    /**
     * @return array LoggerConstantInterface
     */
    protected function getLoggerConstantList()
    {
        $loggerList = array();
        $loggerList[] = new AppLoggerConstant();

        $customLoggerClass = 'CustomBundle\Biz\LoggerConstant';
        if (class_exists($customLoggerClass)) {
            $customLogger = new $customLoggerClass();

            if ($customLogger instanceof LoggerConstantInterface) {
                $loggerList[] = $customLogger;
            }
        }

        $pcm = $this->biz['pluginConfigurationManager'];

        $installedPlugins = $pcm->getInstalledPlugins();

        foreach ($installedPlugins as $installedPlugin) {
            $code = ucfirst($installedPlugin['code']);
            $pluginLoggerClass = "{$code}Plugin\\Biz\\LoggerConstant";
            if (class_exists($pluginLoggerClass)) {
                $pluginLogger = new $pluginLoggerClass();

                if ($pluginLogger instanceof LoggerConstantInterface) {
                    $loggerList[] = $pluginLogger;
                }
            }
        }

        return $loggerList;
    }

    protected function getBrowse()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串

        if (false !== stripos($agent, 'Firefox/')) {
            preg_match("/Firefox\/([^;)]+)+/i", $agent, $version);
            $exp[0] = 'Firefox';
            $exp[1] = $version[1];  //获取火狐浏览器的版本号
        } elseif (false !== stripos($agent, 'Maxthon')) {
            preg_match("/Maxthon\/([\d\.]+)/", $agent, $version);
            $exp[0] = '傲游';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'MSIE')) {
            preg_match("/MSIE\s+([^;)]+)+/i", $agent, $version);
            $exp[0] = 'IE';
            $exp[1] = $version[1];  //获取IE的版本号
        } elseif (false !== stripos($agent, 'OPR')) {
            preg_match("/OPR\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'Opera';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'Edge')) {
            //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
            preg_match("/Edge\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'Edge';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'Chrome')) {
            preg_match("/Chrome\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'Chrome';
            $exp[1] = $version[1];  //获取google chrome的版本号
        } elseif (false !== stripos($agent, 'rv:') && false !== stripos($agent, 'Gecko')) {
            preg_match("/rv:([\d\.]+)/", $agent, $version);
            $exp[0] = 'IE';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'QQBrowser')) {
            preg_match("/QQBrowser([\d\.]+)/", $agent, $version);
            $exp[0] = 'QQ浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'MetaSr')) {
            preg_match("/MetaSr([\d\.]+)/", $agent, $version);
            $exp[0] = '搜狗浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, '360SE')) {
            preg_match("/360SE([\d\.]+)/", $agent, $version);
            $exp[0] = '360浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'safari/')) {
            preg_match('/safari\/([^\s]+)/i', $agent, $version);
            $exp[0] = 'Safari';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'OmniWeb/')) {
            preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $version);
            $exp[0] = 'OmniWeb';
            $exp[1] = $version[1];
        } else {
            $exp[0] = '未知浏览器';
            $exp[1] = '';
        }

        return $exp[0].'('.$exp[1].')';
    }

    protected function getOperatingSystem()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($agent, 'win') && stripos($agent, '95')) {
            $os = 'Windows 95';
        } elseif (stripos($agent, 'win 9x') && stripos($agent, '4.90')) {
            $os = 'Windows ME';
        } elseif (stripos($agent, 'win') && stripos($agent, '98')) {
            $os = 'Windows 98';
        } elseif (stripos($agent, 'win') && stripos($agent, 'nt 5.1')) {
            $os = 'Windows XP';
        } elseif (stripos($agent, 'win') && stripos($agent, 'nt 5')) {
            $os = 'Windows 2000';
        } elseif (stripos($agent, 'win') && stripos($agent, 'nt')) {
            $os = 'Windows NT';
        } elseif (stripos($agent, 'win') && stripos($agent, '32')) {
            $os = 'Windows 32';
        } elseif (stripos($agent, 'linux')) {
            $os = 'Linux';
        } elseif (stripos($agent, 'unix')) {
            $os = 'Unix';
        } elseif (stripos($agent, 'sun') && stripos($agent, 'os')) {
            $os = 'SunOS';
        } elseif (stripos($agent, 'ibm') && stripos($agent, 'os')) {
            $os = 'IBM OS/2';
        } elseif (stripos($agent, 'Mac') && stripos($agent, 'PC')) {
            $os = 'Macintosh';
        } elseif (stripos($agent, 'PowerPC')) {
            $os = 'PowerPC';
        } elseif (stripos($agent, 'AIX')) {
            $os = 'AIX';
        } elseif (stripos($agent, 'HPUX')) {
            $os = 'HPUX';
        } elseif (stripos($agent, 'NetBSD')) {
            $os = 'NetBSD';
        } elseif (stripos($agent, 'BSD')) {
            $os = 'BSD';
        } elseif (stripos($agent, 'OSF1')) {
            $os = 'OSF1';
        } elseif (stripos($agent, 'IRIX')) {
            $os = 'IRIX';
        } elseif (stripos($agent, 'FreeBSD')) {
            $os = 'FreeBSD';
        } elseif (stripos($agent, 'teleport')) {
            $os = 'teleport';
        } elseif (stripos($agent, 'flashget')) {
            $os = 'flashget';
        } elseif (stripos($agent, 'webzip')) {
            $os = 'webzip';
        } elseif (stripos($agent, 'offline')) {
            $os = 'offline';
        } else {
            $os = 'Unknown';
        }

        return $os;
    }

    protected function getDevice()
    {
        $isMobile = false;
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false; // 找不到为flase,否则为TRUE
        }
        // 判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientKeywords = array(
                'mobile',
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match('/('.implode('|', $clientKeywords).')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        if (isset($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((false !== strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml')) && (false === strpos($_SERVER['HTTP_ACCEPT'], 'text/html') || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }

        if ($isMobile) {
            return 'mobile';
        } else {
            return 'computer';
        }
    }

    /**
     * @return LogDao
     */
    protected function getLogDao()
    {
        return $this->createDao('System:LogDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $existsUser = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $userId = $existsUser ? $existsUser['id'] : -1;
            $conditions['userId'] = $userId;
            unset($conditions['nickname']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']);
        }
        if (!empty($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime']);
        }

        if (empty($conditions['level']) || !in_array($conditions['level'], array('info', 'warning', 'error'))) {
            unset($conditions['level']);
        }

        return $conditions;
    }
}
