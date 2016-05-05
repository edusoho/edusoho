<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!empty($user['id'])) {
            $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
        }

        $friendlyLinks = $this->getNavigationService()->getOpenedNavigationsTreeByType('friendlyLink');
        return $this->render('TopxiaWebBundle:Default:index.html.twig', array('friendlyLinks' => $friendlyLinks));
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
            $course          = array();
            $nextLearnLesson = array();
            $progress        = array();
            $teachers        = array();
        }

        return $this->render('TopxiaWebBundle:Default:user-learning.html.twig', array(
            'user'            => $user,
            'course'          => $course,
            'nextLearnLesson' => $nextLearnLesson,
            'progress'        => $progress,
            'teachers'        => $teachers
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

        if (isset($teacher['locked']) && $teacher['locked'] !== '0') {
            $teacher = null;
        }

        return $this->render('TopxiaWebBundle:Default:promoted-teacher-block.html.twig', array(
            'teacher' => $teacher
        ));
    }

    public function latestReviewsBlockAction($number)
    {
        $reviews = $this->getReviewService()->searchReviews(array('private' => 0), 'latest', 0, $number);
        $users   = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));
        return $this->render('TopxiaWebBundle:Default:latest-reviews-block.html.twig', array(
            'reviews' => $reviews,
            'users'   => $users,
            'courses' => $courses
        ));
    }

    public function topNavigationAction($siteNav = null, $isMobile = false)
    {
        $navigations = $this->getNavigationService()->getOpenedNavigationsTreeByType('top');

        return $this->render('TopxiaWebBundle:Default:top-navigation.html.twig', array(
            'navigations' => $navigations,
            'siteNav'     => $siteNav,
            'isMobile'    => $isMobile
        ));
    }

    public function footNavigationAction()
    {
        $navigations = $this->getNavigationService()->findNavigationsByType('foot', 0, 100);

        return $this->render('TopxiaWebBundle:Default:foot-navigation.html.twig', array(
            'navigations' => $navigations
        ));
    }

    public function friendlyLinkAction()
    {
        $friendlyLinks = $this->getNavigationService()->getOpenedNavigationsTreeByType('friendlyLink');

        return $this->render('TopxiaWebBundle:Default:friend-link.html.twig', array(
            'friendlyLinks' => $friendlyLinks
        ));
    }

    public function customerServiceAction()
    {
        $customerServiceSetting = $this->getSettingService()->get('customerService', array());

        return $this->render('TopxiaWebBundle:Default:customer-service-online.html.twig', array(
            'customerServiceSetting' => $customerServiceSetting
        ));
    }

    public function jumpAction(Request $request)
    {
        $courseId = intval($request->query->get('id'));

        if ($this->getCourseService()->isCourseTeacher($courseId, $this->getCurrentUser()->id)) {
            $url = $this->generateUrl('live_course_manage_replay', array('id' => $courseId));
        } else {
            $url = $this->generateUrl('course_show', array('id' => $courseId));
        }

        $jumpScript = "<script type=\"text/javascript\"> if (top.location !== self.location) {top.location = \"{$url}\";}</script>";
        return new Response($jumpScript);
    }

    public function coursesCategoryAction(Request $request)
    {
        $conditions             = $request->query->all();
        $conditions['status']   = 'published';
        $conditions['parentId'] = 0;
        $categoryId             = isset($conditions['categoryId']) ? $conditions['categoryId'] : 0;
        $orderBy                = $conditions['orderBy'];
        $courseType             = isset($conditions['courseType']) ? $conditions['courseType'] : 'course';

        $config = $this->getThemeService()->getCurrentThemeConfig();

        if (!empty($config['confirmConfig'])) {
            $config = $config['confirmConfig']['blocks']['left'];

            foreach ($config as $template) {
                if ($template['code'] == 'course-grid-with-condition-index' && $courseType == 'course') {
                    $config = $template;
                } elseif ($template['code'] == 'open-course' && $courseType == 'open-course') {
                    $config = $template;
                }
            }

            $config['orderBy']    = $orderBy;
            $config['categoryId'] = $categoryId;

            return $this->render('TopxiaWebBundle:Default:'.$config['code'].'.html.twig', array(
                'config' => $config
            ));
        } else {
            return $this->render('TopxiaWebBundle:Default:course-grid-with-condition-index.html.twig', array(
                'categoryId' => $categoryId,
                'orderBy'    => $orderBy
            ));
        }
    }

    protected function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100).'%';

        return array(
            'percent' => $percent,
            'number'  => $member['learnedNum'],
            'total'   => $course['lessonNum']
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

    protected function getBatchNotificationService()
    {
        return $this->getServiceKernel()->createService('User.BatchNotificationService');
    }

    protected function getThemeService()
    {
        return $this->getServiceKernel()->createService('Theme.ThemeService');
    }

    private function getBlacklistService()
    {
        return $this->getServiceKernel()->createService('User.BlacklistService');
    }
}
