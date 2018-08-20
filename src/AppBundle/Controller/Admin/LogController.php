<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\System\Util\LogDataUtils;

class LogController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        if (isset($conditions['hasSystemOperation']) && 0 == $conditions['hasSystemOperation']) {
            $systemUser = $this->getUserService()->getUserByType('system');
            $conditions['exceptedUserId'] = $systemUser['id'];
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

        return $this->render('admin/system/log/logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
            'hasSystemOperation' => empty($conditions['exceptedUserId']) ? 1 : 0,
        ));
    }

    public function logFieldChangeAction(Request $request)
    {
        $log = $request->query->get('log');
        $data = $log['data'];
        $showData = array();

        foreach ($data as $message => $fieldChange) {
            if (is_array($fieldChange)) {
                $key = LogDataUtils::trans($message, $log['module'], $log['action']);

                $fieldChange = self::getStrChangeFiled($log['module'], $log['action'], $fieldChange, $message);

                $showData[$key] = $fieldChange;
            }
        }

        return $this->render('admin/system/log/data-modal.html.twig', array(
            'data' => $showData,
        ));
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
        $transConfigs = LogDataUtils::getTransConfig();
        $getValueConfig = LogDataUtils::getValueConfig();
        foreach ($logs as &$log) {
            $transJsonData = array();
            $logData = $log['data'];
            $log['urlParamsJson'] = array();
            $log['shouldShowModal'] = LogDataUtils::shouldShowModal($log['module'], $log['action']);
            $log['shouldShowTemplate'] = true;

            $getValue = $getValueConfig;
            $getGenerateUrl = array();

            if (array_key_exists($log['module'], $transConfigs)) {
                if (array_key_exists($log['action'], $transConfigs[$log['module']])) {
                    $transConfig = $transConfigs[$log['module']][$log['action']];
                    if (!empty($transConfig['getValue'])) {
                        $getValue = $transConfig['getValue'];
                    }

                    if (!empty($transConfig['generateUrl'])) {
                        $getGenerateUrl = $transConfig['generateUrl'];
                    }
                }
            }

            foreach ($getValue as $key => $value) {
                $transJsonDataValue = $this->getArrayValueByConventKey($value, $logData);
                if (false === $transJsonDataValue) {
                    $log['shouldShowTemplate'] = false;
                    $log['shouldShowModal'] = false;
                    continue;
                }
                $transJsonData[$key] = $transJsonDataValue;
            }

            foreach ($getGenerateUrl as $key => $urlConfig) {
                $urlParam = array();
                foreach ($urlConfig['param'] as $param => $value) {
                    $urlParamValue = $this->getArrayValueByConventKey($value, $logData);
                    if (false === $urlParamValue) {
                        $log['shouldShowTemplate'] = false;
                        $log['shouldShowModal'] = false;
                        continue 2;
                    }
                    $urlParam[$param] = $urlParamValue;
                }
                $transJsonData[$key] = $this->generateUrl($urlConfig['path'], $urlParam);
            }

            $log['urlParamsJson'] = $transJsonData;
        }

        return $logs;
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

        return $data;
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
        $actions = array();
        if (!empty($module)) {
            $actions = $this->getLogService()->getActionsByModule($module);
        }

        return $this->render('admin/system/log/log-action-options.html.twig', array(
            'module' => $module,
            'actions' => $actions,
        ));
    }

    public function usernameMatchUsersAction(Request $request)
    {
        $nickname = $request->query->get('nickname');
        $conditions = array(
            'nickname' => $nickname,
        );
        $orderBy = array('createdTime' => 'ASC');
        $existsUser = $this->getUserService()->searchUsers($conditions, $orderBy, 0, 10);

        return $this->createJsonResponse($existsUser);
    }

    public function prodAction(Request $request)
    {
        $logfile = $this->container->getParameter('kernel.root_dir').'/logs/prod.log';
        if (file_exists($logfile)) {
            $logs = $this->readFileLastLines($logfile, 2000);
        } else {
            $logs = '';
        }

        return $this->render('admin/system/log/logs-prod.html.twig', array(
            'logs' => $logs,
        ));
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

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
