<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

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
}
