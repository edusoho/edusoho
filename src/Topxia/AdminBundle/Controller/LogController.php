<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class LogController extends BaseController {

    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'startDateTime'=>'',
            'endDateTime'=>'',
            'nickname'=>'',
            'level'=>''
        );

        if(!empty($fields)){
            $conditions =$fields;
        }

        $paginator = new Paginator(
            $this->get('request'),
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

        return $this->render('TopxiaAdminBundle:System:logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
            'users' => $users
        ));

    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');        
    }

}