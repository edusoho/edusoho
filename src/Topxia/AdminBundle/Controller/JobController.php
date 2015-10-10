<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class JobController extends BaseController {

    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $fields = ArrayToolkit::filter($fields,array(
            'nextExcutedStartTime'=>'',
            'nextExcutedEndTime'=>'',
            'name'=>'',
            'cycle'=>''
        ));
        $paginator = new Paginator(
            $this->get('request'),
            $this->getJobService()->searchJobsCount($fields),
            30
        );

        $jobs = $this->getJobService()->searchJobs(
            $fields, 
            'created', 
            $paginator->getOffsetCount(), 
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:System:jobs.html.twig', array(
            'jobs' => $jobs,
            'paginator' => $paginator
        ));

    }

    protected function getjobService()
    {
        return $this->getServiceKernel()->createService('Crontab.CrontabService');        
    }

}