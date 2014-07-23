<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class OperationAnalysisController extends BaseController
{
	public function registerAction(Request $request,$tab)
	{	
		$fields=$request->query->all();
		$timeRange=$this->getRegisterFilters($fields);

        $paginator = new Paginator(
            $request,
            $this->getUserService()->searchUserCount($timeRange),
            20
        );

        $registerDetail=$this->getUserService()->searchUsers(
        	$timeRange,
        	array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $registerData="";
        if($tab=="trend"){
        	$registerData=$this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'],$timeRange['endTime']);
        }
        print_r($registerData);
		return $this->render("TopxiaAdminBundle:OperationAnalysis:register.html.twig",array(
			'registerDetail'=>$registerDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
		));
	}

	private function getRegisterFilters($fields)
	{
		if(isset($fields['type']))
		{
			if($fields['type']=="month")return array('startTime'=>strtotime(date("Y-m",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));

			if($fields['type']=="lastMonth")return array('startTime'=>strtotime(date("Y-m", strtotime("-1 month"))),'endTime'=>strtotime(date("Y-m",time())));

			if($fields['type']=="lastThreeMonths")return array('startTime'=>strtotime(date("Y-m", strtotime("-2 month"))),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));
		}

		if(isset($fields['startTime'])&&isset($fields['endTime'])&&$fields['startTime']!=""&&$fields['endTime']!="")
		{
			return array('startTime'=>strtotime($fields['startTime']),'endTime'=>(strtotime($fields['endTime'])+24*3600));
		}

		return array('startTime'=>strtotime(date("Y-m",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));
	}
}