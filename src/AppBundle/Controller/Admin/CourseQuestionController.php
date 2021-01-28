<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Symfony\Component\HttpFoundation\Request;

class CourseQuestionController extends BaseController
{
    public function indexAction(Request $request, $postStatus)
    {
        $conditions = $request->query->all();

        if (isset($conditions['keywordType']) && $conditions['keywordType'] == 'courseTitle') {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['keyword']);
            $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            $conditions['courseSetIds'] = !empty($conditions['courseSetIds']) ? $conditions['courseSetIds'] : array(-1);
        }

        $conditions['type'] = 'question';
        if ($postStatus == 'unPosted') {
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

        return $this->render('admin/course-question/index.html.twig', array(
            'paginator' => $paginator,
            'questions' => $questions,
            'users' => $users,
            'courseSets' => $courseSets,
            'courses' => $courses,
            'tasks' => $tasks,
            'type' => $postStatus,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getThreadService()->deleteThread($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ?: array() as $id) {
            $this->getThreadService()->deleteThread($id);
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

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

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
