<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class CourseQuestionController extends BaseController
{
    public function indexAction(Request $request, $postStatus)
    {
        $conditions = $request->query->all();

        if (isset($conditions['keywordType']) && 'courseTitle' == $conditions['keywordType']) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['keyword']);
            $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            $conditions['courseSetIds'] = !empty($conditions['courseSetIds']) ? $conditions['courseSetIds'] : array(-1);
        }

        $conditions['type'] = 'question';
        if ('unPosted' == $postStatus) {
            $conditions['postNum'] = 0;
        }
        $paginator = new Paginator(
            $request,
            $this->getThreadService()->countThreads($conditions),
            20
        );

        $questions = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($questions, 'courseSetId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($questions, 'taskId'));

        return $this->render('admin-v2/teach/course-question/index.html.twig', array(
            'paginator' => $paginator,
            'questions' => $questions,
            'users' => $users,
            'courseSets' => $courseSets,
            'courses' => $courses,
            'tasks' => $tasks,
            'type' => $postStatus,
        ));
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
