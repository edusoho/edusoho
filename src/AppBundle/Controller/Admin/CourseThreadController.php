<?php
namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseThreadController extends BaseController
{

    public function indexAction(Request $request)
    {

        $conditions = $request->query->all();

        if (isset($conditions['keywordType']) && $conditions['keywordType'] == 'courseTitle') {
            $courses = $this->getCourseService()->findCoursesByLikeTitle(trim($conditions['keyword']));
            $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
            if (count($conditions['courseIds']) == 0) {
                return $this->render('admin/course-thread/index.html.twig', array(
                    'paginator' => new Paginator($request, 0, 20),
                    'threads' => array(),
                    'users'=> array(),
                    'courseSets' => array(),
                    'courses' => array(),
                    'tasks' => array(),
                ));
            }
        }

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
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($threads, 'courseSetId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $tasks = $this->getCourseService()->findTasksByIds(ArrayToolkit::column($threads, 'taskId'));

        return $this->render('admin/course-thread/index.html.twig', array(
            'paginator' => $paginator,
            'threads' => $threads,
            'users'=> $users,
            'courseSets' => $courseSets,
            'courses' => $courses,
            'tasks' => $tasks,
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
        foreach ($ids ? : array() as $id) {
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
