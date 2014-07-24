<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

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
	             $paginator->getPerPageCount()
	             );

	        $registerData="";
	        if($tab=="trend"){
	        	$registerData=$this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'],$timeRange['endTime']);
	       	$zeroData=$this->setZeroDataByDate($timeRange['startTime'],$timeRange['endTime']-24*3600,$fields);
	       	$registerData=ArrayToolkit::index($registerData,'date');

	       	$registerData=array_merge($zeroData,$registerData);

	       	foreach ($registerData as $key => $value) {
	       		$data[]=$value;
	       	}
	       	print_r($data);
	        }
	  
	       return $this->render("TopxiaAdminBundle:OperationAnalysis:register.html.twig",array(
			'registerDetail'=>$registerDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
	      ));
	}

	private function setZeroDataByDate($date,$endDate,$fields)
	{
		$data=array();
		$year=date("Y",$date);
		$month=date("m",$date);
		$day=date("d",time());
		if(isset($fields['type'])){

			if($fields['type']=="month"){
				$data=$this->setCurrentMonthData($year,$month,$day);

				return $data;
			}
			if($fields['type']=="lastMonth"){
				$data=$this->setData($year,$month);

				return $data;
			}
			if($fields['type']=="lastThreeMonths"){
				$dataTwoMonthAgo=$this->setData($year,$month);

				$dataOneMonthAgo=$this->setData(date("Y",strtotime("-1 month")),date("m",strtotime("-1 month")));
				
				$data=$this->setCurrentMonthData(date("Y",time()),date("m",time()),$day);

				$data=array_merge($dataTwoMonthAgo,$dataOneMonthAgo,$data);

				return $data;
			}
			
		}

		if(isset($fields['startTime'])&&isset($fields['endTime'])){

			$monthNum=((date('m',$endDate)-date('m',$date))+12*(date('Y',$endDate)-date('Y',$date)))+1;

			for($i=1;$i<=$monthNum;$i++){
				$dataCurrent=array();
				$j=$i-1;
				$unixEndDate=strtotime("-".$j." month",strtotime(date("Y-m",$endDate)));
				if($i==1&&$monthNum==1){
					$dataCurrent=$this->setCurrentMonthDataWithTimeRange(date("Y",$endDate),date("m",$endDate),date("d",$date),date("d",$endDate));

				}elseif ($i==1){
					$dataCurrent=$this->setCurrentMonthData(date("Y",$endDate),date("m",$endDate),date("d",$endDate));

				}elseif ($i==$monthNum) {
					$dataCurrent=$this->setLastMonthData(date("Y",$date),date("m",$date),date("d",$date));
				
				}else{
					$dataCurrent=$this->setData(date("Y",$unixEndDate),date("m",$unixEndDate));
				}
				$data=array_merge($data,$dataCurrent);

			}
			return $data;

		}
		$data=$this->setCurrentMonthData($year,$month,$day);

		return $data;
	}

	private function setCurrentMonthData($year,$month,$day)
	{
		if($day>=10){
			for($i=1;$i<10;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-0'.$i);
			}
			for($i=10;$i<=$day;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-'.$i);
			}
		}else{
			for($i=1;$i<=$day;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-0'.$i);
			}
		}

		return ArrayToolkit::index($data,'date');
	}

	private function setCurrentMonthDataWithTimeRange($year,$month,$startDay,$endDay)
	{
		if($startDay>=10){
			for($i=$startDay;$i<=$endDay;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-'.$i);
			}
			return ArrayToolkit::index($data,'date');

		}elseif($endDay<10){
			for($i=intval($startDay);$i<=intval($endDay);$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-0'.$i);
			}
			return ArrayToolkit::index($data,'date');
		}else{
			for($i=intval($startDay);$i<10;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-0'.$i);
			}
			for($i=10;$i<=$endDay;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-'.$i);
			}
		}

		return ArrayToolkit::index($data,'date');
	}

	private function setLastMonthData($year,$month,$day)
	{
		$dayNum=cal_days_in_month(CAL_GREGORIAN, $month, $year);
		if($day<10){
			for($i=intval($day);$i<10;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-0'.$i);
			}
			for($i=10;$i<$dayNum;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-'.$i);
			}
		}else{
			for($i=$day;$i<=$dayNum;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-'.$i);
			}
		}

		return ArrayToolkit::index($data,'date');
	}

	private function setData($year,$month)
	{
		$data=array();
		$dayNum=cal_days_in_month(CAL_GREGORIAN, $month, $year);
		for($i=1;$i<10;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-0'.$i);
		}
		for($i=10;$i<=$dayNum;$i++){
				$data[$i]=array('count'=>0,'date'=>$year.'-'.$month.'-'.$i);
		}

		return ArrayToolkit::index($data,'date');
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