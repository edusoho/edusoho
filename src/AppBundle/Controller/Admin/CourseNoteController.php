<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class CourseNoteController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        if (isset($conditions['keywordType']) && $conditions['keywordType'] == 'courseTitle') {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['keyword']);
            $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');
            unset($conditions['keywordType'], $conditions['keyword']);
            $conditions['courseSetIds'] = $conditions['courseSetIds'] ?: array(-1);
        }

        $paginator = new Paginator(
            $request,
            $this->getNoteService()->countCourseNotes($conditions),
            20
        );
        $notes = $this->getNoteService()->searchNotes(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($notes, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($notes, 'courseId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($notes, 'courseSetId'));
        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($notes, 'taskId'));

        return $this->render('admin/course-note/index.html.twig', array(
            'notes' => $notes,
            'paginator' => $paginator,
            'users' => $users,
            'tasks' => ArrayToolkit::index($tasks, 'id'),
            'courses' => $courses,
            'courseSets' => $courseSets,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $note = $this->getNoteService()->deleteNote($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids', array());
        $this->getNoteService()->deleteNotes($ids);

        return $this->createJsonResponse(true);
    }

    /**
     * @return NoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

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

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
