<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;

class DefaultController extends BaseController
{
    public function popularCoursesAction(Request $request)
    {
        $dateType = $request->query->get('dateType');

        $map = array();
        $students = $this->getCourseService()->searchMember(array('date'=>$dateType, 'role'=>'student'), 0 , 10000);
        foreach ($students as $student) {
            if (empty($map[$student['courseId']])) {
                $map[$student['courseId']] = 1;
            } else {
                $map[$student['courseId']] ++;
            }
        }
        asort($map, SORT_NUMERIC);
        $map = array_slice($map, 0, 5, true);

        $courses = array();
        foreach ($map as $courseId => $studentNum) {
            $course = $this->getCourseService()->getCourse($courseId);
            $course['addedStudentNum'] = $studentNum;
            $course['addedMoney'] = 0;

            $orders = $this->getOrderService()->searchOrders(array('targetType'=>'course', 'targetId'=>$courseId, 'status' => 'paid', 'date'=>$dateType), 'latest', 0, 10000);

            foreach ($orders as $id => $order) {
                $course['addedMoney'] += $order['amount'];
            }

            $courses[] = $course;
        }

        return $this->render('TopxiaAdminBundle:Default:popular-courses-table.html.twig', array(
            'courses' => $courses
        ));
        
    }

    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:Default:index.html.twig');
    }

    public function latestUsersBlockAction(Request $request)
    {
        $users = $this->getUserService()->searchUsers(array(), array('createdTime', 'DESC'), 0, 5);
        return $this->render('TopxiaAdminBundle:Default:latest-users-block.html.twig', array(
            'users'=>$users,
        ));
    }

    public function operationAnalysisDashbordBlockAction(Request $request)
    {   
        $todayTimeStart=strtotime(date("Y-m-d",time()));
        $todayTimeEnd=strtotime(date("Y-m-d",time()+24*3600));

        $yesterdayTimeStart=strtotime(date("Y-m-d",time()-24*3600));
        $yesterdayTimeEnd=strtotime(date("Y-m-d",time()));

        $todayRegisterNum=$this->getUserService()->searchUserCount(array("startTime"=>$todayTimeStart,"endTime"=>$todayTimeEnd));

        $yesterdayRegisterNum=$this->getUserService()->searchUserCount(array("startTime"=>$yesterdayTimeStart,"endTime"=>$yesterdayTimeEnd));

        $todayLoginNum=$this->getLogService()->analysisLoginNumByTime(strtotime(date("Y-m-d",time())),strtotime(date("Y-m-d",time()+24*3600)));

        $yesterdayLoginNum=$this->getLogService()->analysisLoginNumByTime(strtotime(date("Y-m-d",time()-24*3600)),strtotime(date("Y-m-d",time())));

        $todayCourseNum=$this->getCourseService()->searchCourseCount(array("startTime"=>$todayTimeStart,"endTime"=>$todayTimeEnd));

        $yesterdayCourseNum=$this->getCourseService()->searchCourseCount(array("startTime"=>$yesterdayTimeStart,"endTime"=>$yesterdayTimeEnd));
     
        $todayLessonNum=$this->getCourseService()->searchLessonCount(array("startTime"=>$todayTimeStart,"endTime"=>$todayTimeEnd));

        $yesterdayLessonNum=$this->getCourseService()->searchLessonCount(array("startTime"=>$yesterdayTimeStart,"endTime"=>$yesterdayTimeEnd));
    
        $todayJoinLessonNum=$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$todayTimeStart,"paidEndTime"=>$todayTimeEnd,"status"=>"paid"));

        $yesterdayJoinLessonNum=$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$yesterdayTimeStart,"paidEndTime"=>$yesterdayTimeEnd,"status"=>"paid"));
    
        $todayBuyLessonNum=$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$todayTimeStart,"paidEndTime"=>$todayTimeEnd,"status"=>"paid","amount"=>"0.00"));

        $yesterdayBuyLessonNum=$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$yesterdayTimeStart,"paidEndTime"=>$yesterdayTimeEnd,"status"=>"paid","amount"=>"0.00"));

        $todayFinishedLessonNum=$this->getCourseService()->searchLearnCount(array("startTime"=>$todayTimeStart,"endTime"=>$todayTimeEnd,"status"=>"finished"));

        $yesterdayFinishedLessonNum=$this->getCourseService()->searchLearnCount(array("startTime"=>$yesterdayTimeStart,"endTime"=>$yesterdayTimeEnd,"status"=>"finished"));

        $todayAllVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)),"fileType"=>'video'));

        $yesterdayAllVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time()-24*3600)),'endTime'=>strtotime(date("Y-m-d",time())),"fileType"=>'video'));        

        $todayCloudVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)),"fileType"=>'video','fileStorage'=>'cloud'));

        $yesterdayCloudVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time()-24*3600)),'endTime'=>strtotime(date("Y-m-d",time())),"fileType"=>'video','fileStorage'=>'cloud'));

        $todayLocalVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)),"fileType"=>'video','fileStorage'=>'local'));

        $yesterdayLocalVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time()-24*3600)),'endTime'=>strtotime(date("Y-m-d",time())),"fileType"=>'video','fileStorage'=>'local'));

        $todayNetVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)),"fileType"=>'video','fileStorage'=>'net'));

        $yesterdayNetVideoViewedNum=$this->getCourseService()->searchAnalysisLessonViewCount(array('startTime'=>strtotime(date("Y-m-d",time()-24*3600)),'endTime'=>strtotime(date("Y-m-d",time())),"fileType"=>'video','fileStorage'=>'net'));

        $todayExitLessonNum=$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$todayTimeStart,"paidEndTime"=>$todayTimeEnd,"statusPaid"=>"paid","statusCreated"=>"created"));

        $yesterdayExitLessonNum=$this->getOrderService()->searchOrderCount(array("paidStartTime"=>$yesterdayTimeStart,"paidEndTime"=>$yesterdayTimeEnd,"statusPaid"=>"paid","statusCreated"=>"created"));

        $todayIncome=$this->getOrderService()->analysisAmount(array("paidStartTime"=>strtotime(date("Y-m-d",time())),"paidEndTime"=>strtotime(date("Y-m-d",time()+24*3600)),"status"=>"paid"))+0.00;

        $yesterdayIncome=$this->getOrderService()->analysisAmount(array("paidStartTime"=>strtotime(date("Y-m-d",time()-24*3600)),"paidEndTime"=>strtotime(date("Y-m-d",time())),"status"=>"paid"))+0.00;

        $todayCourseIncome=$this->getOrderService()->analysisAmount(array("paidStartTime"=>strtotime(date("Y-m-d",time())),"paidEndTime"=>strtotime(date("Y-m-d",time()+24*3600)),"status"=>"paid","targetType"=>"course"))+0.00;

        $yesterdayCourseIncome=$this->getOrderService()->analysisAmount(array("paidStartTime"=>strtotime(date("Y-m-d",time()-24*3600)),"paidEndTime"=>strtotime(date("Y-m-d",time())),"status"=>"paid","targetType"=>"course"))+0.00;

        $storageSetting = $this->getSettingService()->get('storage');

        if (!empty($storageSetting['cloud_access_key']) and !empty($storageSetting['cloud_secret_key'])) {
            $factory = new CloudClientFactory();
            $client = $factory->createClient($storageSetting);
            $keyCheckResult = $client->checkKey();
        } else {
            $keyCheckResult = array('error' => 'error');
        }

        return $this->render('TopxiaAdminBundle:Default:operation-analysis-dashbord.html.twig', array(
            'todayRegisterNum'=>$todayRegisterNum,
            'yesterdayRegisterNum'=>$yesterdayRegisterNum,
            'todayLoginNum'=>$todayLoginNum,
            'yesterdayLoginNum'=>$yesterdayLoginNum,
            'todayCourseNum'=>$todayCourseNum,
            'yesterdayCourseNum'=>$yesterdayCourseNum,
            'todayLessonNum'=>$todayLessonNum,
            'yesterdayLessonNum'=>$yesterdayLessonNum,
            'todayJoinLessonNum'=>$todayJoinLessonNum,
            'yesterdayJoinLessonNum'=>$yesterdayJoinLessonNum,
            'todayBuyLessonNum'=>$todayBuyLessonNum,
            'yesterdayBuyLessonNum'=>$yesterdayBuyLessonNum,
            'todayFinishedLessonNum'=>$todayFinishedLessonNum,
            'yesterdayFinishedLessonNum'=>$yesterdayFinishedLessonNum,

            'todayAllVideoViewedNum'=>$todayAllVideoViewedNum,
            'yesterdayAllVideoViewedNum'=>$yesterdayAllVideoViewedNum,

            'todayCloudVideoViewedNum'=>$todayCloudVideoViewedNum,
            'yesterdayCloudVideoViewedNum'=>$yesterdayCloudVideoViewedNum,

            'todayLocalVideoViewedNum'=>$todayLocalVideoViewedNum,
            'yesterdayLocalVideoViewedNum'=>$yesterdayLocalVideoViewedNum,

            'todayNetVideoViewedNum'=>$todayNetVideoViewedNum,
            'yesterdayNetVideoViewedNum'=>$yesterdayNetVideoViewedNum,

            'todayIncome'=>$todayIncome,
            'yesterdayIncome'=>$yesterdayIncome,
            'todayCourseIncome'=>$todayCourseIncome,
            'yesterdayCourseIncome'=>$yesterdayCourseIncome,
            'todayExitLessonNum'=>$todayExitLessonNum,
            'yesterdayExitLessonNum'=>$yesterdayExitLessonNum,
            'keyCheckResult'=>$keyCheckResult,
        ));        
    }

    public function onlineCountAction(Request $request)
    {
        $onlineCount =  $this->getStatisticsService()->getOnlineCount(15*60);
        return $this->createJsonResponse(array('onlineCount' => $onlineCount, 'message' => 'ok'));
    }

    public function loginCountAction(Request $request)
    {
        $loginCount = $this->getStatisticsService()->getloginCount(15*60);
        return $this->createJsonResponse(array('loginCount' => $loginCount, 'message' => 'ok'));
    }

    public function unsolvedQuestionsBlockAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(
            array('type' => 'question', 'postNum' => 0),
            'createdNotStick',
            0,5
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $askers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $teacherIds = array();
        foreach (ArrayToolkit::column($courses, 'teacherIds') as $teacherId) {
             $teacherIds = array_merge($teacherIds,$teacherId);
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);        

        return $this->render('TopxiaAdminBundle:Default:unsolved-questions-block.html.twig', array(
            'questions'=>$questions,
            'courses'=>$courses,
            'askers'=>$askers,
            'teachers'=>$teachers
        ));
    }

    public function latestPaidOrdersBlockAction(Request $request)
    {
        $orders = $this->getOrderService()->searchOrders(array('status'=>'paid'), 'latest', 0 , 5);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));
        
        return $this->render('TopxiaAdminBundle:Default:latest-paid-orders-block.html.twig', array(
            'orders'=>$orders,
            'users'=>$users,
        ));
    }

    public function questionRemindTeachersAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $question = $this->getThreadService()->getThread($courseId, $questionId);
        $questionUrl = $this->generateUrl('course_thread_show', array('courseId'=>$course['id'], 'id'=> $question['id']), true);
        $questionTitle = strip_tags($question['title']);
        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'default',
                "课程《{$course['title']}》有新问题 <a href='{$questionUrl}' target='_blank'>{$questionTitle}</a>，请及时回答。");
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getStatisticsService()
    {
        return $this->getServiceKernel()->createService('System.StatisticsService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

}
