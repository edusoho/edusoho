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
      
        $feild['status']='published';
        $feild['expired']='0';//0表示开放报名。


        $lastActivitys=$this->getActivityService()->searchActivitys($feild,'last',0,4);


        $feild['recommended']=1;//1表示置顶。
        
        $recommended=$this->getActivityService()->searchActivitys($feild,'recommendedTime-DESC',0,1);
        
        $recommended=count($recommended)>0?$recommended[0]:array('largePicture' =>'',
                                                                    'subtitle'=>'',
                                                                    'title'=>'',
                                                                    'startTime'=>'',
                                                                    'locationId'=>'1',
                                                                    'address'=>'北京.海淀区海淀西大街70号.3W咖啡二楼',
                                                                    'id'=>'0');

        $activitTerchar=empty($recommended['experterId'])?null:$this->getUserService()->findUsersByIds($recommended['experterId']);
        $activitTerchar=count($activitTerchar)>0?current($activitTerchar):null;
        //地址
        $Locations=$this->getLocationService()->getAllLocations();
        //公开课报名用户
        $feild['roles']='ROLE_USER';
        $users=$this->getUserService()->searchUsers($feild,array('createdTime','DESC'),0,38);
        //视频资源库
        $conditions = array('status' => 'published');
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 8);

        //问题讨论
        $activityThreads=$this->getActivityThreadService()->searchThreads(array(),'createdNotStick',0,5);
        $activityIds=ArrayToolkit::column($activityThreads,'activityId');
        $activitys=$this->getActivityService()->findActivityByIds($activityIds);
        
        $threadUserIds=ArrayToolkit::column($activityThreads,'userId');
        $threadUsers=$this->getUserService()->findUsersByIds($threadUserIds);
      
               

        //最新点评
        $reviews=$this->getReviewService()->searchReviews(array(),'latest',0,4);
        $reviewUserIds=ArrayToolkit::column($reviews,'userId');
        $reviewUsers=$this->getUserService()->findUsersByIds($reviewUserIds);
       

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
            "recommended"=>$recommended,
            "lastActivitys"=>$lastActivitys,
            "activitTerchar"=>$activitTerchar,
            "users"=>$users,
            "Locations"=>$Locations,
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

        return $this->render('TopxiaWebBundle:Default:foot-navigation-osf.html.twig', array(
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
