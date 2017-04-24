<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Dao\LogDao;
use Biz\User\Service\UserService;
use Biz\Common\Logger;
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
                    $sort = array('createdTime' => 'DESC');
                    break;
                case 'createdByAsc':
                    $sort = array('createdTime' => 'ASC');
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
                'module' => Logger::getModule($module),
                'action' => $action,
                'message' => $message,
                'data' => empty($data) ? '' : json_encode($data),
                'userId' => $user['id'],
                'ip' => $user['currentIp'],
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

    public function getLogModuleDicts()
    {
        $moduleDicts = Logger::getLogModuleDict();
        $modules = $this->getLogModules();

        $dealModuleDicts = array();
        foreach ($modules as $module) {
            if (in_array($module, array_keys($moduleDicts))) {
                $dealModuleDicts[$module] = $moduleDicts[$module];
            }
        }

        return $dealModuleDicts;
    }

    public function findLogActionDictsyModule($module)
    {
        $systemActions = Logger::systemModuleConfig();
        $pluginActions = Logger::pluginModuleConfig();

        $actions = array_merge($systemActions, $pluginActions);

        if (isset($actions[$module])) {
            return $actions[$module];
        }

        return array();
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

    private function getLogModules()
    {
        $systemModules = array_keys(Logger::systemModuleConfig());
        $pluginModules = array_keys(Logger::pluginModuleConfig());

        $rootDir = realpath($this->biz['root_directory']);

        $filepath = $rootDir.'/config/plugin.php';

        $plugins = array();
        if (file_exists($filepath)) {
            $plugins = require $filepath;
        }

        $plugins = array_map('strtolower', array_keys($plugins));

        if (empty($plugins)) {
            return $systemModules;
        }

        foreach ($pluginModules as $key => $module) {
            $formatModule = str_replace('_', '', $module);
            if (!in_array($formatModule, $plugins)) {
                unset($pluginModules[$key]);
            }
        }

        $modules = array_merge($systemModules, $pluginModules);

        return $modules;
    }
}
