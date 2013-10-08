<?php

namespace Topxia\WebBundle\Controller;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        //$template = ucfirst($this->setting('site.homepage_template', 'less'));
        //return $this->forward("TopxiaWebBundle:Default:index{$template}");
        //下一期公开课
        $feild['istimeout']=0;//0表示未结束。
        $feild['status']='published';//0表示未开始并未结束。

        $nextActivity=$this->getActivityService()->searchActivitys($feild,'latest',0,1);
        
        $nextActivity=count($nextActivity)>0?$nextActivity[0]:array('largePicture' =>'',
                                                                    'subtitle'=>'',
                                                                    'title'=>'',
                                                                    'startTime'=>'',
                                                                    'locationId'=>'0',
                                                                    'id'=>0);

        $activitTerchar=empty($nextActivity['experterId'])?null:$this->getUserService()->findUsersByIds($nextActivity['experterId']);
        $activitTerchar=count($activitTerchar)>0?current($activitTerchar):null;
        //地址
        $Locations=$this->getLocationService()->getAllLocations();
        //公开课报名用户
        $feild['roles']='ROLE_USER';
        $users=$this->getUserService()->searchUsers($feild,array('createdTime','DESC'),0,42);
        //视频资源库
        $conditions = array('status' => 'published');
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 8);

        //问题讨论
        $activityThreads=$this->getActivityThreadService()->searchThreads(array(),'createdNotStick',0,5);
        $activityIds=ArrayToolkit::column($activityThreads,'activityId');
        $activitys=$this->getActivityService()->findActivityByIds($activityIds);
        $threadUserIds=ArrayToolkit::column($activityThreads,'userId');
        $threadUsers=$this->getUserService()->findUsersByIds($threadUserIds);
        $threadUsers[0]=array(
            "id"=>0,
            "nickname"=>"游客");
        
        //大家正在学 ---加入行为过滤器
        $userActions=$this->getUserActionService()->searchUserActions(array(),array('createTime','DESC'),0,6);
        $userActionIds=ArrayToolkit::column($userActions,'userId');
        $userActionUsers=$this->getUserService()->findUsersByIds($userActionIds);
        $userActionUsers[0]=array(
            "id"=>0,
            "nickname"=>"游客");

        //最新点评
        $reviews=$this->getReviewService()->searchReviews(array(),'latest',0,4);
        $reviewUserIds=ArrayToolkit::column($reviews,'userId');
        $reviewUsers=$this->getUserService()->findUsersByIds($reviewUserIds);
        $reviewUsers[0]=array(
            "id"=>0,
            "nickname"=>"游客");
        $reviewCourseIds=ArrayToolkit::column($reviews,'courseId');
        $reviewCourse=$this->getCourseService()->findCoursesByIds($reviewCourseIds);


        //新加入学员
        $feild['roles']='ROLE_USER';
        $students=$this->getUserService()->searchUsers($feild,array('createdTime','DESC'),0,16);
        //开源教练组
        $feild['roles']='ROLE_TEACHER';
        $teachers=$this->getUserService()->searchUsers($feild,array('createdTime','DESC'),0,5);
        $teacherIds=ArrayToolkit::column($teachers,'id');
        $teacherinfos=$this->getUserService()->findUserProfilesByIds($teacherIds);

        return $this->render('TopxiaWebBundle:Default:index.html.twig',array(
            "nextActivity"=>$nextActivity,
            "activitTerchar"=>$activitTerchar,
            "users"=>$users,
            "Locations"=>$Locations,
            "userActions"=>$userActions,
            "userActionUsers"=>$userActionUsers,
            "courses"=>$courses,
            "activityThreads"=>$activityThreads,
            "activitys"=>$activitys,
            "threadUsers"=>$threadUsers,
            "reviews"=>$reviews,
            "reviewUsers"=>$reviewUsers,
            "reviewCourse"=>$reviewCourse,
            "students"=>$students,
            "teachers"=>$teachers,
            "teacherinfos"=>$teacherinfos
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

        return $this->render('TopxiaWebBundle:Default:index-more.html.twig', array(
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

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
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
