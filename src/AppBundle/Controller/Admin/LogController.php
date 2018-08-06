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
        $modules = $this->getLogService()->getModules();
        $module = isset($conditions['module']) ? $conditions['module'] : '';
        $actions = $this->getLogService()->getActionsByModule($module);

        return $this->render('admin/system/log/logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
        ));
    }

    public function logFieldChangeAction(Request $request)
    {
        $log = $request->query->get('log');
        $data = $log['data'];
        $showData = array();

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $key = LogDataUtils::trans($k, $log['module'], $log['action']);
                if (!isset($v['old'])) {
                    $v['old'] = '';
                }
                if (!isset($v['new'])) {
                    $v['new'] = '';
                }
                $v['old'] = $this->tryTrans($log['module'], $log['action'], $v['old'], $k);
                $v['new'] = $this->tryTrans($log['module'], $log['action'], $v['new'], $k);

                $showData[$key] = $v;
            }
        }

        return $this->render('admin/system/log/data-modal.html.twig', array(
            'data' => $showData,
        ));
    }

    private function logsSetUrlParamsJson($logs)
    {
        $transConfigs = LogDataUtils::getTransConfig();
        foreach ($logs as $k => &$v) {
            $transJsonData = array();
            $logData = $v['data'];
            $v['urlParamsJson'] = array();
            $v['shouldShowModal'] = false;
            $v['shouldShowTemplate'] = false;
            if (array_key_exists($v['module'], $transConfigs)) {
                if (array_key_exists($v['action'], $transConfigs[$v['module']])) {
                    $transConfig = $transConfigs[$v['module']][$v['action']];

                    if (isset($transConfig['getValue'])) {
                        foreach ($transConfig['getValue'] as $kk => $vv) {
                            $transJsonData[$kk] = $logData[$vv];
                        }
                    }
                    if (isset($transConfig['generateUrl'])) {
                        foreach ($transConfig['generateUrl'] as $kk => $vv) {
                            $urlConfig = $vv;

                            $urlParam = array();
                            foreach ($urlConfig['param'] as $kkk => $vvv) {
                                $urlParam[$kkk] = $logData[$vvv];
                            }

                            $transJsonData[$kk] = $this->generateUrl($urlConfig['path'], $urlParam);
                        }
                    }

                    $v['urlParamsJson'] = $transJsonData;
                    $v['shouldShowModal'] = LogDataUtils::shouldShowModal($v['module'], $v['action']);
                    $v['shouldShowTemplate'] = true;
                }
            }
        }

        return $logs;
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
}
