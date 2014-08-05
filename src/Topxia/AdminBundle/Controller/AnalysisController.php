<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class AnalysisController extends BaseController
{   

    public function rountByanalysisDateTypeAction(Request $request,$tab)
    {   
        $analysisDateType=$request->query->get("analysisDateType");
        return $this->forward('TopxiaAdminBundle:Analysis:'.$analysisDateType, array(
            'request'=>$request,
            'tab'=>$tab,
        ));
    }

    public function registerAction(Request $request,$tab)
    {      
        $data=array();
        $registerStartDate="";

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
        
        $registerStartData=$this->getUserService()->searchUsers(array(),array('createdTime', 'ASC'),0,1);

        if($registerStartData) $registerStartDate=date("Y-m-d",$registerStartData[0]['createdTime']);

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:register.html.twig",array(
            'registerDetail'=>$registerDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            "registerStartDate"=>$registerStartDate,
            "dataInfo"=>$dataInfo,
          ));
    }

    public function loginAction(Request $request,$tab)
    {       
        $data=array();
        $loginStartDate="";

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

        $loginDetail=$this->getLogService()->searchLogs(
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

        $userIds = ArrayToolkit::column($loginDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $loginStartData=$this->getLogService()->searchLogs(array('action'=>"login_success"),'createdByAsc',0,1);

        if($loginStartData) $loginStartDate=date("Y-m-d",$loginStartData[0]['createdTime']);

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:login.html.twig",array(
            'loginDetail'=>$loginDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'users'=>$users,
            'loginStartDate'=>$loginStartDate,
            'dataInfo'=>$dataInfo,        
        ));
    }
    
    public function courseAction(Request $request,$tab)
    {       
        $data=array();
        $courseStartDate="";

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

        $courseDetail=$this->getCourseService()->searchCourses(
            $timeRange,
            '',
              $paginator->getOffsetCount(),
              $paginator->getPerPageCount()
         );

        $courseData="";

        if($tab=="trend"){
            $courseData=$this->getCourseService()->analysisCourseDataByTime($timeRange['startTime'],$timeRange['endTime']);
    
            $data=$this->fillAnalysisData($condition,$courseData);          
        }

        $userIds = ArrayToolkit::column($courseDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseDetail, 'categoryId'));

        $courseStartData=$this->getCourseService()->searchCourses(array(),'createdTimeByAsc',0,1);

        if($courseStartData) $courseStartDate=date("Y-m-d",$courseStartData[0]['createdTime']);

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:course.html.twig",array(
            'courseDetail'=>$courseDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'categories'=>$categories,
            'data'=>$data,
            'users'=>$users,
            'courseStartDate'=>$courseStartDate,
            'dataInfo'=>$dataInfo,          
         ));
    }


    public function lessonAction(Request $request,$tab)
    {       
        $data=array();
        $lessonStartDate="";

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

        $lessonDetail=$this->getCourseService()->searchLessons(
            $timeRange,
            array('createdTime',"desc"),
              $paginator->getOffsetCount(),
              $paginator->getPerPageCount()
         );

        $lessonData="";

        if($tab=="trend"){
            $lessonData=$this->getCourseService()->analysisLessonDataByTime($timeRange['startTime'],$timeRange['endTime']);
    
            $data=$this->fillAnalysisData($condition,$lessonData);          
        }

        $courseIds = ArrayToolkit::column($lessonDetail, 'courseId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($courses, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $lessonStartData=$this->getCourseService()->searchLessons(array(),array('createdTime',"asc"),0,1);

        if($lessonStartData) $lessonStartDate=date("Y-m-d",$lessonStartData[0]['createdTime']);

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:lesson.html.twig",array(
            'lessonDetail'=>$lessonDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'users'=>$users,
            'lessonStartDate'=>$lessonStartDate,
            'dataInfo'=>$dataInfo,         
        ));
    }

    public function joinLessonAction(Request $request,$tab)
    {       
        $data=array();
        $joinLessonStartDate="";

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
 
        $joinLessonStartData=$this->getOrderService()->searchOrders(array("status"=>"paid"),"early",0,1);

        foreach ($joinLessonStartData as $key) {
            $joinLessonStartDate=date("Y-m-d",$key['createdTime']);
        }         

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:join-lesson.html.twig",array(
            'JoinLessonDetail'=>$JoinLessonDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'users'=>$users,
            'joinLessonStartDate'=>$joinLessonStartDate,
            'dataInfo'=>$dataInfo,      
        ));
    }

    public function exitLessonAction(Request $request,$tab)
    {       
        $data=array();
        $exitLessonStartDate="";

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
                $this->getOrderService()->searchOrderCount(array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"statusPaid"=>"paid","statusCreated"=>"created")),
                20
        );

        $exitLessonDetail=$this->getOrderService()->searchOrders(
            array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"statusPaid"=>"paid","statusCreated"=>"created"),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
         );

        $exitLessonData="";
        if($tab=="trend"){
            $exitLessonData=$this->getOrderService()->analysisExitCourseDataByTimeAndStatus($timeRange['startTime'],$timeRange['endTime']);
  
            $data=$this->fillAnalysisData($condition,$exitLessonData);          
        }

        $courseIds = ArrayToolkit::column($exitLessonDetail, 'targetId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($exitLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $cancelledOrders=$this->getOrderService()->findRefundsByIds(ArrayToolkit::column($exitLessonDetail, 'refundId'));

        $cancelledOrders=ArrayToolkit::index($cancelledOrders,'id');

        $exitLessonStartData=$this->getOrderService()->searchOrders(array("statusPaid"=>"paid","statusCreated"=>"created"),"early",0,1);

        foreach ($exitLessonStartData as $key) {
            $exitLessonStartDate=date("Y-m-d",$key['createdTime']);
        }

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:exit-lesson.html.twig",array(
            'exitLessonDetail'=>$exitLessonDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'users'=>$users,
            'exitLessonStartDate'=>$exitLessonStartDate,
            'cancelledOrders'=>$cancelledOrders,
            'dataInfo'=>$dataInfo,        
        ));
    }

    public function paidLessonAction(Request $request,$tab)
    {
        $data=array();
        $paidLessonStartDate="";

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

        $paidLessonDetail=$this->getOrderService()->searchOrders(
            array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","amount"=>"0.00"),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
         );

        $paidLessonData="";
        if($tab=="trend"){
            $paidLessonData=$this->getOrderService()->analysisPaidCourseOrderDataByTime($timeRange['startTime'],$timeRange['endTime']);
    
            $data=$this->fillAnalysisData($condition,$paidLessonData);          
        }

        $courseIds = ArrayToolkit::column($paidLessonDetail, 'targetId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($paidLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);
                
        $paidLessonStartData=$this->getOrderService()->searchOrders(array("status"=>"paid","amount"=>"0.00"),"early",0,1);

        foreach ($paidLessonStartData as $key) {
            $paidLessonStartDate=date("Y-m-d",$key['createdTime']);
        }

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:paid-lesson.html.twig",array(
            'paidLessonDetail'=>$paidLessonDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'users'=>$users,
            'paidLessonStartDate'=>$paidLessonStartDate,
            'dataInfo'=>$dataInfo,      
        ));
    }

    public function finishedLessonAction(Request $request,$tab)
    {
        $data=array();
        $finishedLessonStartDate="";

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

        $finishedLessonDetail=$this->getCourseService()->searchLearns(
            array("startTime"=>$timeRange['startTime'],"endTime"=>$timeRange['endTime'],"status"=>"finished"),
            array("finishedTime","DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
         );

        $finishedLessonData="";
        if($tab=="trend"){
            $finishedLessonData=$this->getCourseService()->analysisLessonFinishedDataByTime($timeRange['startTime'],$timeRange['endTime']);

            $data=$this->fillAnalysisData($condition,$finishedLessonData);          
        }

        $courseIds = ArrayToolkit::column($finishedLessonDetail, 'courseId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        $lessonIds = ArrayToolkit::column($finishedLessonDetail, 'lessonId');

        $lessons=$this->getCourseService()->findLessonsByIds($lessonIds);

        $userIds = ArrayToolkit::column($finishedLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $finishedLessonStartData=$this->getCourseService()->searchLearns(array("status"=>"finished"),array("finishedTime","ASC"),0,1);

        if($finishedLessonStartData) $finishedLessonStartDate=date("Y-m-d",$finishedLessonStartData[0]['finishedTime']);
        
        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:finished-lesson.html.twig",array(
            'finishedLessonDetail'=>$finishedLessonDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'lessons'=>$lessons,
            'users'=>$users,
            'finishedLessonStartDate'=>$finishedLessonStartDate,
            'dataInfo'=>$dataInfo,          
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

        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('all');

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:video-view.html.twig",array(
            'videoViewedDetail'=>$videoViewedDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'lessons'=>$lessons,
            'users'=>$users,
            'dataInfo'=>$dataInfo,
            'minCreatedTime'=>date("Y-m-d",$minCreatedTime['createdTime']),
            'showHelpMessage' => 1
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
        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('cloud');

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:cloud-video-view.html.twig",array(
            'videoViewedDetail'=>$videoViewedDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'lessons'=>$lessons,
            'users'=>$users,
            'dataInfo'=>$dataInfo,
            'minCreatedTime'=>date("Y-m-d",$minCreatedTime['createdTime']),
            'showHelpMessage' => 1
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
        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('local');
        
        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:local-video-view.html.twig",array(
            'videoViewedDetail'=>$videoViewedDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'lessons'=>$lessons,
            'users'=>$users,
            'dataInfo'=>$dataInfo,
            'minCreatedTime'=>date("Y-m-d",$minCreatedTime['createdTime']),
            'showHelpMessage' => 1
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
        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('net');
        
        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:net-video-view.html.twig",array(
            'videoViewedDetail'=>$videoViewedDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'lessons'=>$lessons,
            'users'=>$users,
            'minCreatedTime'=>date("Y-m-d",$minCreatedTime['createdTime']),
            'dataInfo'=>$dataInfo,
            'showHelpMessage' => 1
        ));
    }

    public function incomeAction(Request $request,$tab)
    {
        $data=array();
        $incomeStartDate="";

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

        $incomeDetail=$this->getOrderService()->searchOrders(
            array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","amount"=>"0.00"),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
         );

        $incomeData="";

        if($tab=="trend"){
            $incomeData=$this->getOrderService()->analysisAmountDataByTime($timeRange['startTime'],$timeRange['endTime']);
    
            $data=$this->fillAnalysisData($condition,$incomeData);          
        }

        $courseIds = ArrayToolkit::column($incomeDetail, 'targetId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($incomeDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);
        
        $incomeStartData=$this->getOrderService()->searchOrders(array("status"=>"paid","amount"=>"0.00"),"early",0,1);
                  
        foreach ($incomeStartData as $key) {
            $incomeStartDate=date("Y-m-d",$key['createdTime']);
        }

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:income.html.twig",array(
            'incomeDetail'=>$incomeDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'users'=>$users,
            'incomeStartDate'=>$incomeStartDate,
            'dataInfo'=>$dataInfo,    
        ));
    }

    public function courseIncomeAction(Request $request,$tab)
    {
        $data=array();
        $courseIncomeStartDate="";

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

        $courseIncomeDetail=$this->getOrderService()->searchOrders(
            array("paidStartTime"=>$timeRange['startTime'],"paidEndTime"=>$timeRange['endTime'],"status"=>"paid","targetType"=>"course","amount"=>'0.00'),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
         );

        $courseIncomeData="";

        if($tab=="trend"){
            $courseIncomeData=$this->getOrderService()->analysisCourseAmountDataByTime($timeRange['startTime'],$timeRange['endTime']);

            $data=$this->fillAnalysisData($condition,$courseIncomeData);            
        }

        $courseIds = ArrayToolkit::column($courseIncomeDetail, 'targetId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($courseIncomeDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $courseIncomeStartData=$this->getOrderService()->searchOrders(array("status"=>"paid","amount"=>"0.00","targetType"=>"course"),"early",0,1);

        foreach ($courseIncomeStartData as $key) {
            $courseIncomeStartDate=date("Y-m-d",$key['createdTime']);
        }

        $dataInfo=$this->getDataInfo($condition,$timeRange);
        return $this->render("TopxiaAdminBundle:OperationAnalysis:courseIncome.html.twig",array(
            'courseIncomeDetail'=>$courseIncomeDetail,
            'paginator'=>$paginator,
            'tab'=>$tab,
            'data'=>$data,
            'courses'=>$courses,
            'users'=>$users,
            'courseIncomeStartDate'=>$courseIncomeStartDate,
            'dataInfo'=>$dataInfo,              
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

        $currentData=array_merge($zeroData,$currentData );

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

    private function getDataInfo($condition,$timeRange)
    {
        return array(            
            'startTime'=>date("Y-m-d",$timeRange['startTime']),
            'endTime'=>date("Y-m-d",$timeRange['endTime']-24*3600),
            'currentMonthStart'=>date("Y-m-d",strtotime(date("Y-m",time()))),
            'currentMonthEnd'=>date("Y-m-d",strtotime(date("Y-m-d",time()))),
            'lastMonthStart'=>date("Y-m-d",strtotime(date("Y-m", strtotime("-1 month")))),
            'lastMonthEnd'=>date("Y-m-d",strtotime(date("Y-m",time()))-24*3600),
            'lastThreeMonthsStart'=>date("Y-m-d",strtotime(date("Y-m", strtotime("-2 month")))),
            'lastThreeMonthsEnd'=>date("Y-m-d",strtotime(date("Y-m-d",time()))),
            'analysisDateType'=>$condition["analysisDateType"],);
    }

    private function getTimeRange($fields)
    {
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