<?php

namespace Biz\System\Service\Impl;

use AppBundle\Common\DeviceToolkit;
use Biz\AppLoggerConstant;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\LoggerConstantInterface;
use Biz\System\Dao\LogDao;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;

class LogServiceImpl extends BaseService implements LogService
{
    const LOG_DATA_LENGTH_LIMIT = 4096;

    public function info($module, $action, $message, $data = null)
    {
        return $this->addLog('info', $module, $action, $message, $data);
    }

    public function warning($module, $action, $message, $data = null)
    {
        return $this->addLog('warning', $module, $action, $message, $data);
    }

    public function error($module, $action, $message, $data = null)
    {
        return $this->addLog('error', $module, $action, $message, $data);
    }

    public function searchLogs($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        if (!is_array($sort)) {
            switch ($sort) {
                case 'created':
                    $sort = ['id' => 'DESC'];
                    break;
                case 'createdByAsc':
                    $sort = ['id' => 'ASC'];
                    break;
                default:
                    $this->createNewException(CommonException::ERROR_PARAMETER());
            }
        }

        $logs = $this->getLogDao()->search($conditions, $sort, $start, $limit);

        foreach ($logs as &$log) {
            $log['data'] = empty($log['data']) ? [] : json_decode($log['data'], true);
            unset($log);
        }

        return $logs;
    }

    public function searchOldLogs($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        if (!is_array($sort)) {
            switch ($sort) {
                case 'created':
                    $sort = ['id' => 'DESC'];
                    break;
                case 'createdByAsc':
                    $sort = ['id' => 'ASC'];
                    break;
                default:
                    $this->createNewException(CommonException::ERROR_PARAMETER());
            }
        }

        $logs = $this->getLogOldDao()->search($conditions, $sort, $start, $limit);

        foreach ($logs as &$log) {
            $log['data'] = empty($log['data']) ? [] : json_decode($log['data'], true);
            unset($log);
        }

        return $logs;
    }

    public function searchLogCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogDao()->count($conditions);
    }

    public function searchOldLogCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogOldDao()->count($conditions);
    }

    protected function addLog($level, $module, $action, $message, $data = null)
    {
        $user = $this->getCurrentUser();

        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_string($value) && mb_strlen($value, 'UTF-8') > self::LOG_DATA_LENGTH_LIMIT) {
                    $value = mb_substr($value, 0, self::LOG_DATA_LENGTH_LIMIT, 'UTF-8');
                }
            }

            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        return $this->getLogDao()->create(
            [
                'module' => $module,
                'action' => $action,
                'message' => $message,
                'data' => empty($data) ? '' : $data,
                'userId' => $user['id'],
                'ip' => $user['currentIp'],
                'browser' => DeviceToolkit::getBrowse(),
                'operatingSystem' => DeviceToolkit::getOperatingSystem(),
                'device' => DeviceToolkit::isMobileClient() ? 'mobile' : 'computer',
                'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'createdTime' => time(),
                'level' => $level,
            ]
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
        $modules = [];
        foreach ($loggerConstantList as $loggerConstant) {
            $modules = array_merge($modules, $loggerConstant->getModules());
        }

        return $modules;
    }

    public function getActionsByModule($module)
    {
        $loggerConstantList = $this->getLoggerConstantList();
        $actions = [];
        foreach ($loggerConstantList as $loggerConstant) {
            $actions = array_merge($actions, $loggerConstant->getActions());
        }

        return $actions[$module] ?? [];
    }

    /**
     * @return array LoggerConstantInterface
     */
    protected function getLoggerConstantList()
    {
        $loggerList = [];
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

    /**
     * @return LogDao
     */
    protected function getLogDao()
    {
        return $this->createDao('System:LogDao');
    }

    /**
     * @return LogDao
     */
    protected function getLogOldDao()
    {
        return $this->createDao('System:LogOldDao');
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

        if (empty($conditions['level']) || !in_array($conditions['level'], ['info', 'warning', 'error'])) {
            unset($conditions['level']);
        }

        return $conditions;
    }
}
