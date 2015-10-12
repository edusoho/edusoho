<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Topxia\System;
use Topxia\Common\Paginator;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        $conditions = array('status' => 'published', 'parentId' => 0, 'recommended' => 1);
        $courses = $this->getCourseService()->searchCourses($conditions, 'recommendedSeq', 0, 12);
        $orderBy = 'recommendedSeq';
        if (empty($courses)) {
            $orderBy = 'latest';
            unset($conditions['recommended']);
            $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 12);
        }


        $coinSetting=$this->getSettingService()->get('coin',array());
        if(isset($coinSetting['cash_rate'])){
            $cashRate=$coinSetting['cash_rate'];
        }else{
            $cashRate=1;
        }

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!empty($courseSetting['live_course_enabled']) && $courseSetting['live_course_enabled']) {
            $recentLiveCourses = $this->getRecentLiveCourses();
        } else {
            $recentLiveCourses = array();
        }
        $categories = $this->getCategoryService()->findGroupRootCategories('course');
        
        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));

        return $this->render('TopxiaWebBundle:Default:index.html.twig', array(
            'courses' => $courses,
            'categories' => $categories,
            'blocks' => $blocks,
            'recentLiveCourses' => $recentLiveCourses,
            'consultDisplay' => true,
            'cashRate' => $cashRate,
            'orderBy' => $orderBy
        ));
    }

    public function userlearningAction()
    {
        $user = $this->getCurrentUser();

        $courses = $this->getCourseService()->findUserLearnCourses($user->id, 0, 1);

        if (!empty($courses)) {
            foreach ($courses as $course) {
                $member = $this->getCourseService()->getCourseMember($course['id'], $user->id);

                $teachers = $this->getUserService()->findUsersByIds($course['teacherIds']);
            }

            $nextLearnLesson = $this->getCourseService()->getUserNextLearnLesson($user->id, $course['id']);

            $progress = $this->calculateUserLearnProgress($course, $member);
        } else {
            $course = array();
            $nextLearnLesson = array();
            $progress = array();
            $teachers = array();
        }

        return $this->render('TopxiaWebBundle:Default:user-learning.html.twig', array(
                'user' => $user,
                'course' => $course,
                'nextLearnLesson' => $nextLearnLesson,
                'progress'  => $progress,
                'teachers' => $teachers
            ));
    }

    protected function getRecentLiveCourses()
    {

        $recenntLessonsCondition = array(
            'status' => 'published',
            'endTimeGreaterThan' => time(),
        );

        $recentlessons = $this->getCourseService()->searchLessons(
            $recenntLessonsCondition,  
            array('startTime', 'ASC'),
            0,
            20
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($recentlessons, 'courseId'));

        $liveCourses = array();
        foreach ($recentlessons as $lesson) {
            $course = $courses[$lesson['courseId']];
            if ($course['status'] != 'published') {
                continue;
            }
            if($course['parentId'] != 0){
                continue;   
            }
            $course['lesson'] = $lesson;
            $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);
            if (count($liveCourses) >= 8) {
                break;
            }
            $liveCourses[] = $course;
        }
        return  $liveCourses;
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

        if(isset($teacher['locked']) && $teacher['locked'] !== '0'){
            $teacher = null;
        }

        return $this->render('TopxiaWebBundle:Default:promoted-teacher-block.html.twig', array(
            'teacher' => $teacher,
        ));
    }

    public function latestReviewsBlockAction($number)
    {
        $reviews = $this->getReviewService()->searchReviews(array('private' => 0), 'latest', 0, $number);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));
        return $this->render('TopxiaWebBundle:Default:latest-reviews-block.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
        ));
    }

    public function topNavigationAction($siteNav = null,$isMobile= false)
    {
        $navigations = $this->getNavigationService()->getOpenedNavigationsTreeByType('top');

        return $this->render('TopxiaWebBundle:Default:top-navigation.html.twig', array(
            'navigations' => $navigations,
            'siteNav' => $siteNav,
            'isMobile' => $isMobile
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

    public function jumpAction(Request $request)
    {
        $courseId = intval($request->query->get('id'));
        if($this->getCourseService()->isCourseTeacher($courseId, $this->getCurrentUser()->id)){
            $url = $this->generateUrl('live_course_manage_replay', array('id' => $courseId));
        }else{
            $url = $this->generateUrl('course_show', array('id' => $courseId));
        }
        $jumpScript = "<script type=\"text/javascript\"> if (top.location !== self.location) {top.location = \"{$url}\";}</script>";
        return new Response($jumpScript);
    }

    public function CoursesCategoryAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        $categoryId = $conditions['categoryId'];
        if ($conditions['categoryId']  != 'all') {
            $conditions['categoryId'] = intval($conditions['categoryId']);
        }
        else{
            unset($conditions['categoryId']);
        }
        $orderBy = $conditions['orderBy'];
        if ($orderBy == 'recommendedSeq') {  
           $conditions['recommended'] = 1; 
        }
        unset($conditions['orderBy']);

        $courses = $this->getCourseService()->searchCourses($conditions,$orderBy, 0, 12);

        return $this->render('TopxiaWebBundle:Default:course-grid-with-condition.html.twig',array(
            'orderBy' => $orderBy,
            'categoryId' => $categoryId,
            'courses' => $courses
        ));
    }


    protected function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum']
        );
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

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getClassroomService() 
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getBlacklistService() 
    {
        return $this->getServiceKernel()->createService('User.BlacklistService');
    }
}
