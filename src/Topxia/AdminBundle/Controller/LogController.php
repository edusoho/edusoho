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

    public function prodAction(Request $request)
    {
        $logfile = $this->container->getParameter('kernel.root_dir') . '/logs/prod.log';
        if (file_exists($logfile)) {
            $logs = $this->readFileLastLines($logfile, 2000);
        } else {
            $logs = '';
        }

        return $this->render('TopxiaAdminBundle:System:logs-prod.html.twig', array(
            'logs' => $logs,
        ));
    }

    protected function readFileLastLines($filename, $n){
        if(!$fp=fopen($filename,'r')) {
            throw new \RuntimeException("打开文件失败，请检查文件路径是否正确，路径和文件名不要包含中文");
        }
        $pos=-2;
        $eof="";
        $str="";
        while($n>0){
            while($eof!="\n") {
                if(!fseek($fp,$pos,SEEK_END)) {
                    $eof=fgetc($fp);
                    $pos--;
                }else{
                    break;
                }
            }
            $str.=fgets($fp);
            $eof="";
            $n--;
        }
        return $str;
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');        
    }

}