<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService as CourseThreadService;
use Biz\Thread\Service\ThreadService as ClassroomThreadService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class ThreadController extends BaseController
{
    public function courseThreadIndexAction(Request $request)
    {
        $conditions = $request->query->all();

        if (isset($conditions['keywordType']) && 'courseTitle' == $conditions['keywordType']) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['keyword']);
            $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            $conditions['courseSetIds'] = empty($conditions['courseSetIds']) ? array(-1) : $conditions['courseSetIds'];
        }
        $paginator = new Paginator(
            $request,
            $this->getCourseThreadService()->countThreads($conditions),
            20
        );
        $threads = $this->getCourseThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($threads, 'courseSetId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($threads, 'taskId'));
        $tasks = ArrayToolkit::index($tasks, 'id');

        return $this->render('admin-v2/teach/thread/course-thread.html.twig', array(
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'courseSets' => $courseSets,
            'courses' => $courses,
            'tasks' => $tasks,
        ));
    }

    public function courseThreadDeleteAction(Request $request, $id)
    {
        $this->getCourseThreadService()->deleteThread($id);

        return $this->createJsonResponse(true);
    }

    public function courseThreadBatchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ?: array() as $id) {
            $this->getCourseThreadService()->deleteThread($id);
        }

        return $this->createJsonResponse(true);
    }

    public function classroomThreadIndexAction(Request $request)
    {
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $request,
            $this->getClassroomThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getClassroomThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('admin-v2/teach/thread/classroom-thread.html.twig', array(
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'classrooms' => $classrooms,
        ));
    }

    public function classroomThreadDeleteAction(Request $request, $threadId)
    {
        $this->getClassroomThreadService()->deleteThread($threadId);

        return $this->createJsonResponse(true);
    }

    public function classroomThreadBatchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ?: array() as $id) {
            $this->getClassroomThreadService()->deleteThread($id);
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ClassroomThreadService
     */
    protected function getClassroomThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return CourseThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
