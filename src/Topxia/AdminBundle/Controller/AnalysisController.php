<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class AnalysisController extends BaseController
{
	public function registerAction(Request $request,$tab)
	{		
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);

	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_register', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getUserService()->searchUserCount($timeRange),
		           	 20
	        	);

	        	$registerDetail=$this->getUserService()->searchUsers(
		        	$timeRange,
		        	array('createdTime', 'DESC'),
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$registerData="";
	        	if($tab=="trend"){
		        	$registerData=$this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$registerData);		  	
	        	}
	      	
	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:register.html.twig",array(
			'registerDetail'=>$registerDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
	      ));
	}

	private function fillAnalysisData($condition,$currentData)
	{
		$dates=$this->getDatesByCondition($condition);

		foreach ($dates as $key => $value) {
			
			$zeroData[]=array("date"=>$value,"count"=>0);
		}

	       	$currentData=ArrayToolkit::index($currentData,'date');

	       	$zeroData=ArrayToolkit::index($zeroData,'date');

	       	$currentData=array_merge($zeroData,$currentData	);

	       	foreach ($currentData as $key => $value) {
	       		$data[]=$value;
	       	}
	       	return json_encode($data);
	}

	private function getDatesByCondition($condition)
	{	
		$timeRange=$this->getTimeRange($condition);

		$dates=$this->makeDateRange($timeRange['startTime'],$timeRange['endTime']-24*3600);

		return $dates;
	}
	
	private function getTimeRange($fields)
	{
		if(isset($fields['type']))
		{
			if($fields['type']=="month")return array('startTime'=>strtotime(date("Y-m",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));

			if($fields['type']=="lastMonth")return array('startTime'=>strtotime(date("Y-m", strtotime("-1 month"))),'endTime'=>strtotime(date("Y-m",time())));

			if($fields['type']=="lastThreeMonths")return array('startTime'=>strtotime(date("Y-m", strtotime("-2 month"))),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));
		}

		if(isset($fields['startTime'])&&isset($fields['endTime'])&&$fields['startTime']!=""&&$fields['endTime']!="")
		{	
			if($fields['startTime']>$fields['endTime']) return false;
			return array('startTime'=>strtotime($fields['startTime']),'endTime'=>(strtotime($fields['endTime'])+24*3600));
		}

		return array('startTime'=>strtotime(date("Y-m",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));
	}

	private function makeDateRange($startTime, $endTime)
	{
		$dates = array();

		$currentTime = $startTime;
		while ( true)  {
			if ($currentTime > $endTime) {
				break;
			}
			$currentDate = date('Y-m-d', $currentTime);
			$dates[] = $currentDate;

			$currentTime = $currentTime + 3600 * 24;
		}
		return $dates;
	}
}