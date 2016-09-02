<?php

namespace Topxia\Service\System\Impl;

use Topxia\Common\PluginToolkit;
use Topxia\Service\Common\Logger;
use Topxia\Service\System\LogService;
use Topxia\Service\Common\BaseService;

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

        switch ($sort) {
            case 'created':
                $sort = array('createdTime', 'DESC');
                break;
            case 'createdByAsc':
                $sort = array('createdTime', 'ASC');
                break;

            default:
                throw $this->createServiceException('参数sort不正确。');
                break;
        }

        $logs = $this->getLogDao()->searchLogs($conditions, $sort, $start, $limit);

        foreach ($logs as &$log) {
            $log['data'] = empty($log['data']) ? array() : json_decode($log['data'], true);
            unset($log);
        }

        return $logs;
    }

    public function searchLogCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);
        return $this->getLogDao()->searchLogCount($conditions);
    }

    protected function addLog($level, $module, $action, $message, array $data = null)
    {
        return $this->getLogDao()->addLog(array(
            'module'      => Logger::getModule($module),
            'action'      => $action,
            'message'     => $message,
            'data'        => empty($data) ? '' : json_encode($data),
            'userId'      => $this->getCurrentUser()->id,
            'ip'          => $this->getCurrentUser()->currentIp,
            'createdTime' => time(),
            'level'       => $level
        ));
    }

    protected function getLogDao()
    {
        return $this->createDao('System.LogDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $existsUser           = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $userId               = $existsUser ? $existsUser['id'] : -1;
            $conditions['userId'] = $userId;
            unset($conditions['nickname']);
        }

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']);
            $conditions['endDateTime']   = strtotime($conditions['endDateTime']);
        } else {
            unset($conditions['startDateTime']);
            unset($conditions['endDateTime']);
        }

        if (empty($conditions['level']) || !in_array($conditions['level'], array('info', 'warning', 'error'))) {
            unset($conditions['level']);
        }

        return $conditions;
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
        $modules     = $this->getLogModules();

        $dealModuleDicts = array();
        foreach ($modules as $module) {
            if (in_array($module, array_keys($moduleDicts))) {
                $dealModuleDicts[$module] = $moduleDicts[$module];
            }
        }
        return $dealModuleDicts;
    }

    private function getLogModules()
    {
        $systemModules = array_keys(Logger::systemModuleConfig());
        $pluginModules = array_keys(Logger::pluginModuleConfig());

        $plugins = PluginToolkit::getPlugins();
        if (empty($plugins)) {
            return $systemModules;
        }
        $plugins = array_map('strtolower', array_keys($plugins));

        foreach ($pluginModules as $key => $module) {
            $formatModule = str_replace('_', '', $module);
            if (!in_array($formatModule, $plugins)) {
                unset($pluginModules[$key]);
            }
        }
        if (in_array('homework', $plugins)) {
            $pluginModules[] = 'exercise';
        }

        $modules = array_merge($systemModules, $pluginModules);

        return $modules;
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
}
