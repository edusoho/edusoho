<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class ThreadController extends BaseController
{
    public function teachingAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $myTeachingCourseCount = $this->getCourseService()->findUserTeachCourseCount(array('userId' => $user['id']), true);

        if (empty($myTeachingCourseCount)) {
            return $this->render('my/teaching/threads.html.twig', array(
                'type' => $type,
                'threadType' => 'course',
                'threads' => array(),
            ));
        }

        $myTeachingCourses = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, $myTeachingCourseCount, true);

        $conditions = array(
            'courseIds' => ArrayToolkit::column($myTeachingCourses, 'id'),
            'type' => $type,
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCountInCourseIds($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreadInCourseIds(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $courses = ArrayToolKit::index($courses, 'id');

        $setIds = ArrayToolKit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($setIds);
        $courseSets = ArrayToolKit::index($courseSets, 'id');

        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($threads, 'taskId'));

        return $this->render('my/teaching/threads.html.twig', array(
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'courseSets' => $courseSets,
            'courses' => $courses,
            'tasks' => $tasks,
            'type' => $type,
            'threadType' => 'course',
        ));
    }

    public function discussionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'discussion',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->countThreads($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        return $this->render('my/learning/thread/discussions.html.twig', array(
            'threadType' => 'course',
            'courses' => $courses,
            'users' => $users,
            'threads' => $threads,
            'paginator' => $paginator,
            'courseSets' => $courseSets,
        ));
    }

    public function questionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'question',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->countThreads($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        return $this->render('my/learning/thread/questions.html.twig', array(
            'threadType' => 'course',
            'courses' => $courses,
            'users' => $users,
            'threads' => $threads,
            'paginator' => $paginator,
            'courseSets' => $courseSets,
        ));
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
