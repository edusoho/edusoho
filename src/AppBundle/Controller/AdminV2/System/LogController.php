<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\System\Service\LogService;
use Biz\System\Util\LogDataUtils;
use Symfony\Component\HttpFoundation\Request;

class LogController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $systemUser = $this->getUserService()->getUserByType('system');
        $conditions['exceptedUserId'] = $systemUser['id'];

        $hasSystemOperation = 0;
        if (isset($conditions['hasSystemOperation']) && 1 == $conditions['hasSystemOperation']) {
            unset($conditions['exceptedUserId']);
            $hasSystemOperation = 1;
        }

        $conditions['excludeActions'] = LogDataUtils::getUnDisplayModuleAction();

        if (isset($conditions['showAllLog']) && 1 == $conditions['showAllLog']) {
            unset($conditions['excludeActions']);
        }

        $paginator = new Paginator(
            $request,
            $this->getLogService()->searchLogCount($conditions),
            30
        );

        $logs = $this->getLogService()->searchLogs(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $logs = $this->logsSetUrlParamsJson($logs);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logs, 'userId'));
        $users = $this->setSystemUserName($users);

        $modules = $this->getLogService()->getModules();
        $module = isset($conditions['module']) ? $conditions['module'] : '';
        $actions = $this->getLogService()->getActionsByModule($module);

        return $this->render('admin-v2/system/log/logs.html.twig', [
            'logs' => $logs,
            'paginator' => $paginator,
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
            'hasSystemOperation' => $hasSystemOperation,
        ]);
    }

    public function oldAction(Request $request)
    {
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $request,
            $this->getLogService()->searchOldLogCount($conditions),
            30
        );

        $logs = $this->getLogService()->searchOldLogs(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logs, 'userId'));
        $users = $this->setSystemUserName($users);

        $modules = $this->getLogService()->getModules();
        $module = isset($conditions['module']) ? $conditions['module'] : '';
        $actions = $this->getLogService()->getActionsByModule($module);

        return $this->render('admin-v2/system/log/logs-old.html.twig', [
            'logs' => $logs,
            'paginator' => $paginator,
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
        ]);
    }

    public function logFieldChangeAction(Request $request)
    {
        $log = $request->query->get('log');
        $data = $log['data'];
        $modalShowFields = [];
        $showData = [];

        $transConfigs = LogDataUtils::getYmlConfig();
        if (array_key_exists($log['module'], $transConfigs)) {
            if (array_key_exists($log['action'], $transConfigs[$log['module']])) {
                $transConfig = $transConfigs[$log['module']][$log['action']];
                if (array_key_exists('modalField', $transConfig)) {
                    $modalShowFields = $transConfig['modalField'];
                }
            }
        }

        if ('all' == $modalShowFields) {
            $modalShowFields = [];
            foreach ($data as $message => $fieldChange) {
                $modalShowFields[] = $message;
            }
        }

        foreach ($data as $message => $fieldChange) {
            $key = LogDataUtils::trans($message, $log['module'], $log['action']);
            if (is_array($fieldChange) && in_array($message, $modalShowFields)) {
                $fieldChange = self::getStrChangeFiled($log['module'], $log['action'], $fieldChange, $message);
                $showData[$key] = $fieldChange;
            }

            if (!is_array($fieldChange) && in_array($message, $modalShowFields)) {
                $showData[$key] = $fieldChange;
            }
        }

        return $this->render('admin-v2/system/log/data-modal.html.twig', [
            'data' => $showData,
        ]);
    }

    private function setSystemUserName($users)
    {
        $systemUser = $this->getUserService()->getUserByType('system');
        foreach ($users as &$user) {
            if ($user['id'] == $systemUser['id']) {
                $user['nickname'] = '系统';
            }
        }

        return $users;
    }

    private function logsSetUrlParamsJson($logs)
    {
        $transConfigs = LogDataUtils::getYmlConfig();
        $getValueDefaultConfig = LogDataUtils::getLogDefaultConfig();
        foreach ($logs as &$log) {
            $transJsonData = [];
            $logData = $log['data'];
            $log['urlParamsJson'] = [];
            $log['shouldShowModal'] = false;
            $log['shouldShowTemplate'] = true;
            if (empty($logData)) {
                continue;
            }

            $templateParam = [];

            if (array_key_exists($log['module'], $transConfigs)) {
                if (array_key_exists($log['action'], $transConfigs[$log['module']])) {
                    $transConfig = $transConfigs[$log['module']][$log['action']];

                    if (array_key_exists('templateParam', $transConfig)) {
                        $templateParam = array_merge($getValueDefaultConfig, $transConfig['templateParam']);
                    }
                    if (array_key_exists('modalField', $transConfig)) {
                        $log['shouldShowModal'] = true;
                    }
                }
            }

            $templateParam = $this->getDefaultTemplateConfig($templateParam, $getValueDefaultConfig);

            foreach ($templateParam as $key => $paramConfig) {
                if (!is_array($paramConfig) || !array_key_exists('type', $paramConfig)) {
                    $transJsonDataValue = $this->getArrayValueByConventKey($paramConfig, $logData);
                    if (false === $transJsonDataValue) {
                        $log['shouldShowTemplate'] = false;
                        $log['shouldShowModal'] = false;
                        continue;
                    }
                    $transJsonData[$key] = $transJsonDataValue;
                } else {
                    if ('url' == $paramConfig['type']) {
                        $urlParam = [];
                        foreach ($paramConfig['param'] as $param => $value) {
                            $urlParamValue = $this->getArrayValueByConventKey($value, $logData);
                            if (false === $urlParamValue) {
                                $log['shouldShowTemplate'] = false;
                                $log['shouldShowModal'] = false;
                                continue 2;
                            }
                            $urlParam[$param] = $urlParamValue;
                        }
                        $transJsonData[$key] = $this->generateUrl($paramConfig['path'], $urlParam);
                    }
                }
            }

            $log['urlParamsJson'] = $transJsonData;
        }

        return $logs;
    }

    private function getDefaultTemplateConfig($templateParam, $defaultConfig)
    {
        foreach ($defaultConfig as $key => $value) {
            if (!array_key_exists($key, $templateParam)) {
                $templateParam[$key] = $value;
            }
        }

        return $templateParam;
    }

    private function getArrayValueByConventKey($keyName, $targetArray)
    {
        $data = '';
        if (is_array($keyName)) {
            foreach ($keyName as $key) {
                if (array_key_exists($key, $targetArray)) {
                    if (!empty($targetArray[$key])) {
                        $data = $targetArray[$key];
                        break;
                    }
                }
            }
        } else {
            $keys = explode('.', $keyName);
            foreach ($keys as $key) {
                if (empty($data)) {
                    if (!array_key_exists($key, $targetArray)) {
                        return false;
                    }
                    $data = $targetArray[$key];
                } else {
                    if (!array_key_exists($key, $data)) {
                        return false;
                    }
                    $data = $data[$key];
                }
            }
        }

        $data = $this->deleteHTMLCode($data);

        return $data;
    }

    private function deleteHTMLCode($str)
    {
        if (!is_string($str)) {
            $str = json_encode($str);
        }

        return strip_tags($str);
    }

    private function getStrChangeFiled($module, $action, $fieldChange, $message)
    {
        if (!isset($fieldChange['old'])) {
            $fieldChange['old'] = '';
        }
        if (!isset($fieldChange['new'])) {
            $fieldChange['new'] = '';
        }

        $fieldChange['old'] = $this->getTransField($module, $action, $fieldChange['old'], $message);
        $fieldChange['new'] = $this->getTransField($module, $action, $fieldChange['new'], $message);

        return $fieldChange;
    }

    private function getTransField($module, $action, $field, $message)
    {
        if (is_array($field)) {
            $field = json_encode($field);
        }
        $field = $this->tryTrans($module, $action, $field, $message);

        return $field;
    }

    private function tryTrans($module, $action, $message, $prefix = '')
    {
        $transMessage = $message;
        if (!empty($prefix)) {
            $transMessage = $prefix.'.'.$transMessage;
        }

        $trans = LogDataUtils::trans($transMessage, $module, $action);

        if ($trans == $transMessage) {
            return $message;
        }

        return $trans;
    }

    public function logActionsAction(Request $request)
    {
        $module = $request->query->get('module');
        $actions = [];
        if (!empty($module)) {
            $actions = $this->getLogService()->getActionsByModule($module);
        }

        return $this->render('admin-v2/system/log/log-action-options.html.twig', [
            'module' => $module,
            'actions' => $actions,
        ]);
    }

    public function usernameMatchUsersAction(Request $request)
    {
        $nickname = $request->query->get('nickname');
        $conditions = [
            'nickname' => $nickname,
        ];
        $orderBy = ['createdTime' => 'ASC'];
        $users = $this->getUserService()->searchUsers($conditions, $orderBy, 0, 10);

        return $this->createJsonResponse($users);
    }

    public function prodAction(Request $request)
    {
        $logfile = $this->container->getParameter('kernel.root_dir').'/logs/prod.log';
        if (file_exists($logfile)) {
            $logs = $this->readFileLastLines($logfile, 2000);
        } else {
            $logs = '';
        }

        return $this->render('admin-v2/system/log/logs-prod.html.twig', [
            'logs' => $logs,
        ]);
    }

    protected function readFileLastLines($filename, $n)
    {
        if (!$fp = fopen($filename, 'r')) {
            throw new \RuntimeException('打开文件失败，请检查文件路径是否正确，路径和文件名不要包含中文');
        }
        $pos = -2;
        $eof = '';
        $str = '';
        while ($n > 0) {
            while ("\n" != $eof) {
                if (!fseek($fp, $pos, SEEK_END)) {
                    $eof = fgetc($fp);
                    --$pos;
                } else {
                    break;
                }
            }
            $str .= fgets($fp);
            $eof = '';
            --$n;
        }

        return $str;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
