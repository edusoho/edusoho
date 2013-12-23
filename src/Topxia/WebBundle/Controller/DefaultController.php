<?php

namespace Topxia\WebBundle\Controller;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Topxia\System;
use Topxia\Common\Paginator;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        //$template = ucfirst($this->setting('site.homepage_template', 'less'));
        //return $this->forward("TopxiaWebBundle:Default:index{$template}");
        //下一期公开课

        $currentuser=$this->getCurrentUser();
        $userId=$currentuser['id'];

       //所有活动
        $conditions['status']='published';
        $conditions['actType']='公开课';
       
        $paginator = new Paginator(
            $this->get('request'),
            $this->getActivityService()->searchActivityCount($conditions)
            , 3
        ); 
        
        $lastActivitys = $this->getActivityService()->searchActivitys(
            $conditions, 'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
      
        $lastActivitys =  $this->getActivityService()->extActivitys($lastActivitys);
        $lastActivitys= $this->getActivityService()->mixActivitys($lastActivitys,$userId);



 
        
       
        //新加入学员
        $feild['roles']='ROLE_USER';
        $users=$this->getUserService()->searchUsers($feild,array('createdTime','DESC'),0,29);
        //
        $conditions = array('status' => 'published','recommended'=>1);
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 6);

        //公开课问题讨论
        $activityThreads=$this->getActivityThreadService()->searchThreads(array(),'createdNotStick',0,5);
        $activityIds=ArrayToolkit::column($activityThreads,'activityId');
        $activitys=$this->getActivityService()->findActivitysByIds($activityIds);
        
        $threadUserIds=ArrayToolkit::column($activityThreads,'userId');
        $threadUsers=$this->getUserService()->findUsersByIds($threadUserIds);
      
        //学习动态
        $studyLogs=$this->getLogService()->searchLogs(array('startDateTime'=>'',
            'endDateTime'=>'','level'=>'','moudule'=>'course','action'=>'course_learning'),'created',0,4);
        $studyLogUserIds=ArrayToolkit::column($studyLogs,'userId');
        $studyLogUsers=$this->getUserService()->findUsersByIds($studyLogUserIds); 

        //答疑动态
        $answerLogs=$this->getLogService()->searchLogs(array('startDateTime'=>'',
            'endDateTime'=>'','level'=>'','moudule'=>'course','action'=>'teacher_post'),'created',0,4);
        $answerLogUserIds=ArrayToolkit::column($answerLogs,'userId');
        $answerLogUsers=$this->getUserService()->findUsersByIds($answerLogUserIds); 
       
        $courseThreads=array();
        foreach ($answerLogs as $key => $value) {
            $courseThreads[]= $value['data'];
        }

        $questionUserIds=ArrayToolkit::column($courseThreads,'userId');
        $questionUsers=$this->getUserService()->findUsersByIds($questionUserIds); 



        //点评动态
        $reviews=$this->getReviewService()->searchReviews(array(),'latest',0,4);
        $reviewUserIds=ArrayToolkit::column($reviews,'userId');
        $reviewUsers=$this->getUserService()->findUsersByIds($reviewUserIds);
        $reviewCourseIds=ArrayToolkit::column($reviews,'courseId');
        $reviewCourses=$this->getCourseService()->findCoursesByIds($reviewCourseIds);


        //笔记动态
        $notes=$this->getNoteService()->searchNotes(array(),'updated',0,4);
        $noteUserIds=ArrayToolkit::column($notes,'userId');
        $noteUsers=$this->getUserService()->findUsersByIds($noteUserIds);
        $noteCourseIds=ArrayToolkit::column($notes,'courseId');
        $noteCourses=$this->getCourseService()->findCoursesByIds($noteCourseIds);


        //开源教练组
        $feild['roles']='ROLE_TEACHER';
        $teachers=$this->getUserService()->searchUsers($feild,array('promotedTime','DESC'),0,9);
        $teacherIds=ArrayToolkit::column($teachers,'id');
        $teacherinfos=$this->getUserService()->findUserProfilesByIds($teacherIds);

        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));

       
        return $this->render('TopxiaWebBundle:Default:index-osf.html.twig',array(
          
            "lastActivitys"=>$lastActivitys,
          
            "users"=>$users,
        
            "courses"=>$courses,

            "activityThreads"=>$activityThreads,
            "activitys"=>$activitys,
            "threadUsers"=>$threadUsers,

            "reviews"=>$reviews,
            "reviewUsers"=>$reviewUsers,
            "reviewCourses"=>$reviewCourses,


            "notes"=>$notes,
            "noteUsers"=>$noteUsers,
            "noteCourses"=>$noteCourses,

            "studyLogs"=>$studyLogs,
            "studyLogUsers"=>$studyLogUsers,

            "answerLogs"=>$answerLogs,
            "answerLogUsers"=>$answerLogUsers,
            "questionUsers"=>$questionUsers,

            "teachers"=>$teachers,
            "teacherinfos"=>$teacherinfos,
            "blocks" => $blocks
            ));
    }

    public function indexLessAction()
    {
        $conditions = array('status' => 'published');
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 12);

        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));

        return $this->render('TopxiaWebBundle:Default:index-less.html.twig', array(
            'courses' => $courses,
            'blocks' => $blocks,
        ));
    }

    public function indexMoreAction()
    {

        $conditions = array('status' => 'published');
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 12);

        $categories = $this->getCategoryService()->findGroupRootCategories('course');

        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));

        return $this->render('TopxiaWebBundle:Default:index.html.twig', array(
            'courses' => $courses,
            'categories' => $categories,
            'blocks' => $blocks
        ));
    }

    public function promotedTeacherBlockAction()
    {
        $teacher = $this->getUserService()->findLatestPromotedTeacher(0, 1);
        if ($teacher) {
            $teacher = $teacher[0];
            $teacher = array_merge(
                $teacher,
                $this->getUserService()->getUserProfile($teacher['id'])
            );
        }

        return $this->render('TopxiaWebBundle:Default:promoted-teacher-block.html.twig', array(
            'teacher' => $teacher,
        ));
    }

    public function latestReviewsBlockAction($number)
    {
        $reviews = $this->getReviewService()->searchReviews(array(), 'latest', 0, $number);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));
        return $this->render('TopxiaWebBundle:Default:latest-reviews-block.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
        ));
    }

    public function topNavigationAction()
    {
    	$navigations = $this->getNavigationService()->findNavigationsByType('top', 0, 100);

    	return $this->render('TopxiaWebBundle:Default:top-navigation.html.twig', array(
    		'navigations' => $navigations,
		));
    }


    public function footNavigationAction()
    {
        $navigations = $this->getNavigationService()->findNavigationsByType('foot', 0, 100);

        return $this->render('TopxiaWebBundle:Default:foot-navigation.html.twig', array(
            'navigations' => $navigations,
        ));
    }

    public function customerServiceAction()
    {
        $customerServiceSetting = $this->getSettingService()->get('customerService', array());

        return $this->render('TopxiaWebBundle:Default:customer-service-online.html.twig', array(
            'customerServiceSetting' => $customerServiceSetting,
        ));

    }

    public function systemInfoAction()
    {
        $info = array(
            'version' => System::VERSION,
        );

        return $this->createJsonResponse($info);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }


    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

     protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getActivityService()
    {
         return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    protected function getActivityThreadService()
    {
        return $this->getServiceKernel()->createService('Activity.ThreadService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getLocationService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.LocationService');
    }

    protected function getUserActionService(){
        return $this->getServiceKernel()->createService('User.UserActionService');
    }


}
