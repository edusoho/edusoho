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

	public function loginAction(Request $request,$tab)
	{		
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_login', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getLogService()->searchLogCount(array('action'=>"login_success",'startDateTime'=>date("Y-m-d H:i:s",$timeRange['startTime']),'endDateTime'=>date("Y-m-d H:i:s",$timeRange['endTime']))),
		           	 20
	        	);

	        	$LoginDetail=$this->getLogService()->searchLogs(
		        	array('action'=>"login_success",'startDateTime'=>date("Y-m-d H:i:s",$timeRange['startTime']),'endDateTime'=>date("Y-m-d H:i:s",$timeRange['endTime'])),
		        	'created',
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$LoginData="";
	        	if($tab=="trend"){
		        	$LoginData=$this->getLogService()->analysisLoginDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$LoginData);		  	
	        	}

	        	$userIds = ArrayToolkit::column($LoginDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);

	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:login.html.twig",array(
			'LoginDetail'=>$LoginDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'users'=>$users,
	     	 ));
	}
	
	public function courseAction(Request $request,$tab)
	{		
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_login', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getCourseService()->searchCourseCount($timeRange),
		           	 20
	        	);

	        	$CourseDetail=$this->getCourseService()->searchCourses(
		        	$timeRange,
		        	'',
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$CourseData="";
	        	if($tab=="trend"){
		        	$CourseData=$this->getCourseService()->analysisCourseDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$CourseData);		  	
	        	}

	        	$userIds = ArrayToolkit::column($CourseDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);

	              $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($CourseDetail, 'categoryId'));

	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:course.html.twig",array(
			'CourseDetail'=>$CourseDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'categories'=>$categories,
			'data'=>$data,
			'users'=>$users
	     	 ));
	}


	public function lessonAction(Request $request,$tab)
	{		
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_login', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getCourseService()->searchLessonCount($timeRange),
		           	 20
	        	);

	        	$LessonDetail=$this->getCourseService()->searchLessons(
		        	$timeRange,
		        	array('createdTime',"desc"),
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$LessonData="";
	        	if($tab=="trend"){
		        	$LessonData=$this->getCourseService()->analysisLessonDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$LessonData);		  	
	        	}

		$courseIds = ArrayToolkit::column($LessonDetail, 'courseId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

	        	$userIds = ArrayToolkit::column($courses, 'userId');

	             $users = $this->getUserService()->findUsersByIds($userIds);

	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:lesson.html.twig",array(
			'LessonDetail'=>$LessonDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'users'=>$users,
	      	));
	}

	public function joinLessonAction(Request $request,$tab)
	{		
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_login', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid")),
		           	 20
	        	);

	        	$JoinLessonDetail=$this->getOrderService()->searchOrders(
		        	array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid"),
		        	"latest",
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$JoinLessonData="";
	        	if($tab=="trend"){
		        	$JoinLessonData=$this->getOrderService()->analysisCourseOrderDataByTimeAndStatus($timeRange['startTime'],$timeRange['endTime'],"paid");
		  	
		  	$data=$this->fillAnalysisData($condition,$JoinLessonData);		  	
	        	}

		$courseIds = ArrayToolkit::column($JoinLessonDetail, 'targetId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

	        	$userIds = ArrayToolkit::column($JoinLessonDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);
	        
	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:join-lesson.html.twig",array(
			'JoinLessonDetail'=>$JoinLessonDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'users'=>$users,
	      	));
	}

	public function exitLessonAction(Request $request,$tab)
	{		
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_login', array(
			               'tab' => "trend",
		                )));
	        	}
		$paginator = new Paginator(
		            	$request,
		            	$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"cancelled")),
		           	 20
	        	);

	        	$ExitLessonDetail=$this->getOrderService()->searchOrders(
		        	array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"cancelled"),
		        	"latest",
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$ExitLessonData="";
	        	if($tab=="trend"){
		        	$ExitLessonData=$this->getOrderService()->analysisCourseOrderDataByTimeAndStatus($timeRange['startTime'],$timeRange['endTime'],"cancelled");
		  	
		  	$data=$this->fillAnalysisData($condition,$ExitLessonData);		  	
	        	}

		$courseIds = ArrayToolkit::column($ExitLessonDetail, 'targetId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

	        	$userIds = ArrayToolkit::column($ExitLessonDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);

	              $cancelledOrders=$this->getOrderService()->findRefundsByIds(ArrayToolkit::column($ExitLessonDetail, 'refundId'));

	              $cancelledOrders=ArrayToolkit::index($cancelledOrders,'id');

	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:exit-lesson.html.twig",array(
			'ExitLessonDetail'=>$ExitLessonDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'users'=>$users,
			'cancelledOrders'=>$cancelledOrders,
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

	protected function getLogService()
	{
		return $this->getServiceKernel()->createService('System.LogService');
	}

	private function getCourseService()
	{
	        	return $this->getServiceKernel()->createService('Course.CourseService');
	}

	private function getCategoryService()
	{
	        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
	 }

	private function getOrderService()
	{
	        return $this->getServiceKernel()->createService('Order.OrderService');
	}
}