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
			'timeRange'=>$timeRange,
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
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_course', array(
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
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_lesson', array(
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
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_join', array(
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
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_exit', array(
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
		        	$ExitLessonData=$this->getOrderService()->analysisExitCourseDataByTimeAndStatus($timeRange['startTime'],$timeRange['endTime'],"success");
		  	
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

	public function paidLessonAction(Request $request,$tab)
	{
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_paid', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","amount"=>"0.00")),
		           	 20
	        	);

	        	$PaidLessonDetail=$this->getOrderService()->searchOrders(
		        	array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","amount"=>"0.00"),
		        	"latest",
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$PaidLessonData="";
	        	if($tab=="trend"){
		        	$PaidLessonData=$this->getOrderService()->analysisPaidCourseOrderDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$PaidLessonData);		  	
	        	}

		$courseIds = ArrayToolkit::column($PaidLessonDetail, 'targetId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

	        	$userIds = ArrayToolkit::column($PaidLessonDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);
	        
	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:paid-lesson.html.twig",array(
			'PaidLessonDetail'=>$PaidLessonDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'users'=>$users,
	      	));
	}

	public function finishedLessonAction(Request $request,$tab)
	{
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_finished', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getCourseService()->searchLearnCount(array("startTime"=>$timeRange['startTime'],"endTime"=>$timeRange['endTime'],"status"=>"finished")),
		           	 20
	        	);

	        	$FinishedLessonDetail=$this->getCourseService()->searchLearns(
		        	array("startTime"=>$timeRange['startTime'],"endTime"=>$timeRange['endTime'],"status"=>"finished"),
		        	array("finishedTime","DESC"),
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$FinishedLessonData="";
	        	if($tab=="trend"){
		        	$FinishedLessonData=$this->getCourseService()->analysisLessonFinishedDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$FinishedLessonData);		  	
	        	}

		$courseIds = ArrayToolkit::column($FinishedLessonDetail, 'courseId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

		$lessonIds = ArrayToolkit::column($FinishedLessonDetail, 'lessonId');

		$lessons=$this->getCourseService()->findLessonsByIds($lessonIds);

	        	$userIds = ArrayToolkit::column($FinishedLessonDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);
	        
	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:finished-lesson.html.twig",array(
			'FinishedLessonDetail'=>$FinishedLessonDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'lessons'=>$lessons,
			'users'=>$users,
	      	));
	}

	public function videoViewedAction(Request $request,$tab)
	{
    	$data=array();
    	$condition=$request->query->all();

    	$timeRange=$this->getTimeRange($condition);

		$searchCondition = array(
			"fileType"=>'video',
			"startTime"=>$timeRange['startTime']
			,"endTime"=>$timeRange['endTime']
		);

    	if(!$timeRange) {
		  $this->setFlashMessage("danger","输入的日期有误!");
			return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
               'tab' => "trend",
            )));
    	}

    	$paginator = new Paginator(
            	$request,
            	$this->getCourseService()->searchAnalysisLessonViewCount(
            		$searchCondition,
           	 20
    	));

    	$videoViewedDetail=$this->getCourseService()->searchAnalysisLessonView(
      		$searchCondition,
    		array("createdTime","DESC"),
              $paginator->getOffsetCount(),
              $paginator->getPerPageCount()
         );
    	$videoViewedTrendData="";

    	if($tab=="trend"){
        	$videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'],$timeRange['endTime'],array("fileType"=>'video'));
  	
		  	$data=$this->fillAnalysisData($condition,$videoViewedTrendData);		  	
    	}

		$lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
		$lessons=$this->getCourseService()->findLessonsByIds($lessonIds);
		$lessons=ArrayToolkit::index($lessons,'id');

    	$userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
		$users = $this->getUserService()->findUsersByIds($userIds);
		$users = ArrayToolkit::index($users,'id');

		$timeRange['endTime'] = $timeRange['endTime']-24*3600;
		$minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime();

       	return $this->render("TopxiaAdminBundle:OperationAnalysis:video-view.html.twig",array(
			'videoViewedDetail'=>$videoViewedDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'lessons'=>$lessons,
			'users'=>$users,
			'timeRange'=>$timeRange,
			'minCreatedTime'=>date("Y-m-d",$minCreatedTime['createdTime']),
      	));
	}

	public function cloudVideoViewedAction(Request $request,$tab)
	{
		$data=array();
    	$condition=$request->query->all();

    	$timeRange=$this->getTimeRange($condition);
    	
			$searchCondition = array(
				"fileType"=>'video',
				"fileStorage"=>'cloud',
				"startTime"=>$timeRange['startTime']
				,"endTime"=>$timeRange['endTime']
			);
    	
    	if(!$timeRange) {
		  $this->setFlashMessage("danger","输入的日期有误!");
			return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
               'tab' => "trend",
            )));
    	}

    	$paginator = new Paginator(
            	$request,
            	$this->getCourseService()->searchAnalysisLessonViewCount(
            		$searchCondition,
           	 20
    	));

    	$videoViewedDetail=$this->getCourseService()->searchAnalysisLessonView(
      		$searchCondition,
    		array("createdTime","DESC"),
              $paginator->getOffsetCount(),
              $paginator->getPerPageCount()
         );

    	$videoViewedTrendData="";

    	if($tab=="trend"){
        	$videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'],$timeRange['endTime'],array("fileType"=>'video',"fileStorage"=>'cloud'));
  	
		  	$data=$this->fillAnalysisData($condition,$videoViewedTrendData);		  	
    	}

		$lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
		$lessons=$this->getCourseService()->findLessonsByIds($lessonIds);
		$lessons=ArrayToolkit::index($lessons,'id');

    	$userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
		$users = $this->getUserService()->findUsersByIds($userIds);
		$users = ArrayToolkit::index($users,'id');

       	return $this->render("TopxiaAdminBundle:OperationAnalysis:cloud-video-view.html.twig",array(
			'videoViewedDetail'=>$videoViewedDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'lessons'=>$lessons,
			'users'=>$users,
      	));
	}

	public function localVideoViewedAction(Request $request,$tab)
	{
		$data=array();
    	$condition=$request->query->all();

    	$timeRange=$this->getTimeRange($condition);
    	
			$searchCondition = array(
				"fileType"=>'video',
				"fileStorage"=>'local',
				"startTime"=>$timeRange['startTime']
				,"endTime"=>$timeRange['endTime']
			);
    	
    	if(!$timeRange) {
		  $this->setFlashMessage("danger","输入的日期有误!");
			return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
               'tab' => "trend",
            )));
    	}

    	$paginator = new Paginator(
            	$request,
            	$this->getCourseService()->searchAnalysisLessonViewCount(
            		$searchCondition,
           	 20
    	));

    	$videoViewedDetail=$this->getCourseService()->searchAnalysisLessonView(
      		$searchCondition,
    		array("createdTime","DESC"),
              $paginator->getOffsetCount(),
              $paginator->getPerPageCount()
         );

    	$videoViewedTrendData="";

    	if($tab=="trend"){
        	$videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'],$timeRange['endTime'],array("fileType"=>'video',"fileStorage"=>'local'));
  	
		  	$data=$this->fillAnalysisData($condition,$videoViewedTrendData);		  	
    	}

		$lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
		$lessons=$this->getCourseService()->findLessonsByIds($lessonIds);
		$lessons=ArrayToolkit::index($lessons,'id');

    	$userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
		$users = $this->getUserService()->findUsersByIds($userIds);
		$users = ArrayToolkit::index($users,'id');

       	return $this->render("TopxiaAdminBundle:OperationAnalysis:local-video-view.html.twig",array(
			'videoViewedDetail'=>$videoViewedDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'lessons'=>$lessons,
			'users'=>$users,
      	));
	}
	
	public function netVideoViewedAction(Request $request,$tab)
	{
		$data=array();
    	$condition=$request->query->all();

    	$timeRange=$this->getTimeRange($condition);
    	
			$searchCondition = array(
				"fileType"=>'video',
				"fileStorage"=>'net',
				"startTime"=>$timeRange['startTime']
				,"endTime"=>$timeRange['endTime']
			);
    	
    	if(!$timeRange) {
		  $this->setFlashMessage("danger","输入的日期有误!");
			return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
               'tab' => "trend",
            )));
    	}

    	$paginator = new Paginator(
            	$request,
            	$this->getCourseService()->searchAnalysisLessonViewCount(
            		$searchCondition,
           	 20
    	));

    	$videoViewedDetail=$this->getCourseService()->searchAnalysisLessonView(
      		$searchCondition,
    		array("createdTime","DESC"),
              $paginator->getOffsetCount(),
              $paginator->getPerPageCount()
         );

    	$videoViewedTrendData="";

    	if($tab=="trend"){
        	$videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'],$timeRange['endTime'],array("fileType"=>'video',"fileStorage"=>'net'));
  	
		  	$data=$this->fillAnalysisData($condition,$videoViewedTrendData);		  	
    	}

		$lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
		$lessons=$this->getCourseService()->findLessonsByIds($lessonIds);
		$lessons=ArrayToolkit::index($lessons,'id');

    	$userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
		$users = $this->getUserService()->findUsersByIds($userIds);
		$users = ArrayToolkit::index($users,'id');

       	return $this->render("TopxiaAdminBundle:OperationAnalysis:net-video-view.html.twig",array(
			'videoViewedDetail'=>$videoViewedDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'lessons'=>$lessons,
			'users'=>$users,
      	));
	}

	public function incomeAction(Request $request,$tab)
	{
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_income', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","amount"=>"0.00")),
		           	 20
	        	);

	        	$IncomeDetail=$this->getOrderService()->searchOrders(
		        	array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","amount"=>"0.00"),
		        	"latest",
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$IncomeData="";
	        	if($tab=="trend"){
		        	$IncomeData=$this->getOrderService()->analysisAmountDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$IncomeData);		  	
	        	}

		$courseIds = ArrayToolkit::column($IncomeDetail, 'targetId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

	        	$userIds = ArrayToolkit::column($IncomeDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);
	        
	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:income.html.twig",array(
			'IncomeDetail'=>$IncomeDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'users'=>$users,
	      	));
	}

	public function courseIncomeAction(Request $request,$tab)
	{
	        	$data=array();
	        	$condition=$request->query->all();
	        	$timeRange=$this->getTimeRange($condition);
       	
	        	if(!$timeRange) {

	        		  $this->setFlashMessage("danger","输入的日期有误!");
        		                return $this->redirect($this->generateUrl('admin_operation_analysis_course_income', array(
			               'tab' => "trend",
		                )));
	        	}
	        	$paginator = new Paginator(
		            	$request,
		            	$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","targetType"=>"course","amount"=>"0.00")),
		           	 20
	        	);

	        	$CourseIncomeDetail=$this->getOrderService()->searchOrders(
		        	array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","targetType"=>"course","amount"=>'0.00'),
		        	"latest",
		              $paginator->getOffsetCount(),
		              $paginator->getPerPageCount()
	             );

	        	$CourseIncomeData="";
	        	if($tab=="trend"){
		        	$CourseIncomeData=$this->getOrderService()->analysisCourseAmountDataByTime($timeRange['startTime'],$timeRange['endTime']);
		  	
		  	$data=$this->fillAnalysisData($condition,$CourseIncomeData);		  	
	        	}

		$courseIds = ArrayToolkit::column($CourseIncomeDetail, 'targetId');

		$courses=$this->getCourseService()->findCoursesByIds($courseIds);

	        	$userIds = ArrayToolkit::column($CourseIncomeDetail, 'userId');

	              $users = $this->getUserService()->findUsersByIds($userIds);
	        
	       	return $this->render("TopxiaAdminBundle:OperationAnalysis:courseIncome.html.twig",array(
			'CourseIncomeDetail'=>$CourseIncomeDetail,
			'paginator'=>$paginator,
			'tab'=>$tab,
			'data'=>$data,
			'courses'=>$courses,
			'users'=>$users,
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