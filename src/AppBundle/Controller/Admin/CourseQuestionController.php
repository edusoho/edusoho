<?php
namespace AppBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseQuestionController extends BaseController
{
    public function indexAction(Request $request, $postStatus)
    {
        $conditions = $request->query->all();
        if (isset($conditions['keywordType']) && $conditions['keywordType'] == 'courseTitle') {
            $courses                 = $this->getCourseService()->findCoursesByLikeTitle(trim($conditions['keyword']));
            $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
            if (count($conditions['courseIds']) == 0) {
                return $this->render('admin/course-question/index.html.twig', array(
                    'paginator' => new Paginator($request, 0, 20),
                    'questions' => array(),
                    'users'     => array(),
                    'courseSets' => array(),
                    'courses'   => array(),
                    'tasks'   => array(),
                    'type'      => $postStatus
                ));
            }
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

        $users   = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($questions, 'courseSetId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $tasks = $this->getCourseService()->findTasksByIds(ArrayToolkit::column($questions, 'taskId'));

        return $this->render('admin/course-question/index.html.twig', array(
            'paginator' => $paginator,
            'questions' => $questions,
            'users'     => $users,
            'courseSets' => $courseSets,
            'courses'   => $courses,
            'tasks'   => $tasks,
            'type'      => $postStatus
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

    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
