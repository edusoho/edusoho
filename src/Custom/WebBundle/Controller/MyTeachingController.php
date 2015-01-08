<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MyTeachingController extends BaseController
{
    public function dashboardAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        return $this->render('CustomWebBundle:MyTeaching:dashboard.html.twig', array(
        ));
    }

    public function myCoursesRatingAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $teachCoursesCount = $this->getCourseService()->findUserTeachCourseCount($user['id']);
        $teachCourses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, $teachCoursesCount);
        $teachCourses = ArrayToolkit::index($teachCourses, 'id');
        return $this->render('CustomWebBundle:MyTeaching:my-courses-rating.html.twig', array(
            'teachCourses' => $teachCourses,
        ));
    }

    public function reviewListAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $previewAs = $request->query->get('previewAs');
        $isModal = $request->query->get('isModal');

        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->getCourseReviewCount($id)
            , 10
        );

        $reviews = $this->getReviewService()->findCourseReviews(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('CustomWebBundle:MyTeaching:course-review-modal.html.twig', array(
            'course' => $course,
            'reviews' => $reviews,
            'users' => $users,
            'isModal' => $isModal,
            'paginator' => $paginator
        ));
    }

    public function myInfoAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $profile = $this->getUserService()->getUserProfile($user['id']);
        return $this->render('CustomWebBundle:MyTeaching:my-information.html.twig', array(
            'user' => $user,
            'profile' => $profile,
        ));
    }   

    public function myServiceAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $myTeachingCourseCount = $this->getCourseService()->findUserTeachCourseCount($user['id'] , true);
        $myTeachingCourses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, $myTeachingCourseCount, true);
        $courseIds = ArrayToolkit::column($myTeachingCourses, 'id');

        $homeWorkResults = $this->waitingCheckHomeworks($user['id'], $courseIds);
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($homeWorkResults,'lessonId'));
        $threads = $this->waitingAnswerTheards($user['id'], $myTeachingCourses);
        $userIds =  array_merge(ArrayToolkit::column($homeWorkResults, 'userId'), ArrayToolkit::column($threads, 'userId'));
        $users = $this->getUserService()->findUsersByIds($userIds);
        $testResults = $this->waitingCheckTests($courseIds);
        return $this->render('CustomWebBundle:MyTeaching:myService.html.twig', array(
            'myTeachingCourses' => $myTeachingCourses,
            'lessons' => $lessons,
            'threads' => $threads,
            'users' => $users,
            'homeWorkResults' => $homeWorkResults,
            'testResults' => $testResults
        ));
    }

    private function waitingAnswerTheards($userId, $courseIds)
    {
        
        $conditions = array(
            'courseIds' => $courseIds,
            'type' => 'question');

        $threads = $this->getThreadService()->searchThreadInCourseIds(
            $conditions,
            'createdNotStick',
            0,
            PHP_INT_MAX
        );

        $threadList=array();
        foreach ($threads as $thread) {
            $elitePosts=$this->getThreadService()->findThreadElitePosts($thread['courseId'], $thread['id'], 0, PHP_INT_MAX);
            if(count($elitePosts)==0){
                $threadList[]=$thread;
            }
        }
        return $threadList;
    }

    private function waitingCheckHomeworks($userId, $courseIds)
    {
        $homeWorkResults = $this->getHomeworkService()->findResultsByCourseIdsAndStatus(
            $courseIds,'reviewing',array('usedTime','DESC'),
            0,
            PHP_INT_MAX
        );

        return $homeWorkResults;
    }

    private function waitingCheckTests($courseIds)
    {
        $testResults = $this->getTestPaperSerice()->findTestpaperResultsByCourseIdsAndStatus($courseIds, 'reviewing', 0, PHP_INT_MAX);
        return $testResults;
    }
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    private function getTestPaperSerice()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}