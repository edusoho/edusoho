<?php

namespace AppBundle\Controller;

use Biz\Course\CourseNoteException;
use Biz\Task\Service\TaskService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseNoteService;
use Symfony\Component\HttpFoundation\Request;

class NoteController extends BaseController
{
    public function listAction(Request $request, $courseIds, $filters)
    {
        $conditions = $this->convertFiltersToConditions($courseIds, $filters);
        $notes = array();
        $result['notes'] = $notes;
        if ((isset($conditions['courseIds']) && !empty($conditions['courseIds'])) ||
            (isset($conditions['courseId']) && !empty($conditions['courseId']))
        ) {
            $paginator = new Paginator(
                $request,
                $this->getNoteService()->countCourseNotes($conditions),
                20
            );
            $orderBy = $this->convertFiltersToOrderBy($filters);
            $notes = $this->getNoteService()->searchNotes(
                $conditions,
                $orderBy,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
            $result = $this->makeNotesRelated($notes, $courseIds);
            $result['paginator'] = $paginator;
        }

        return $this->render('classroom/note/list.html.twig', $result);
    }

    /**
     * create note or update note.
     *
     * @param Request $request
     * @param  $courseId
     * @param  $taskId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveCourseNoteAction(Request $request, $courseId, $taskId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        if ($request->isMethod('POST')) {
            $note = $request->request->all();
            $note['courseId'] = $courseId;
            $note['taskId'] = $taskId;
            $note['status'] = isset($note['status']) && $note['status'] === 'on' ? 1 : 0;
            $note = $this->getNoteService()->saveNote($note);

            return $this->createJsonResponse($note);
        }
    }

    public function likeAction(Request $request, $id)
    {
        $note = $this->getNoteService()->getNote($id);

        if (empty($note)) {
            $this->createNewException(CourseNoteException::NOTFOUND_NOTE());
        }

        return $this->createJsonResponse($this->getNoteService()->like($id));
    }

    public function cancelLikeAction(Request $request, $id)
    {
        $note = $this->getNoteService()->getNote($id);

        if (empty($note)) {
            $this->createNewException(CourseNoteException::NOTFOUND_NOTE());
        }

        return $this->createJsonResponse($this->getNoteService()->cancelLike($id));
    }

    protected function makeNotesRelated($notes, $courseIds)
    {
        $user = $this->getCurrentUser();
        $result = array();
        $noteLikes = $this->getNoteService()->findNoteLikesByNoteIdsAndUserId(ArrayToolkit::column($notes, 'id'), $user['id']);
        $userIds = ArrayToolkit::column($notes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $result['noteLikes'] = $noteLikes;
        $result['users'] = $users;
        $tasksIds = ArrayToolkit::column($notes, 'taskId');
        $tasks = $this->getTaskService()->findTasksByIds($tasksIds);
        $result['tasks'] = $tasks;
        if (is_array($courseIds)) {
            $courseIds = ArrayToolkit::column($notes, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $result['courses'] = $courses;
        }
        $result['notes'] = $notes;

        return $result;
    }

    protected function convertFiltersToConditions($courseIds, $filters)
    {
        $conditions = array(
            'status' => 1,
        );
        if (is_numeric($courseIds)) {
            $conditions['courseId'] = $courseIds;
        }
        if (!empty($filters['courseId'])) {
            $conditions['courseId'] = $filters['courseId'];
        }
        if (is_array($courseIds) && empty($filters['courseId'])) {
            $conditions['courseIds'] = $courseIds;
        }
        if (!empty($filters['taskId'])) {
            $conditions['taskId'] = $filters['taskId'];
        }

        return $conditions;
    }

    protected function convertFiltersToOrderBy($filters)
    {
        $orderBy = array();
        switch ($filters['sort']) {
            case 'latest':
                $orderBy['updatedTime'] = 'DESC';
                break;
            case 'likeNum':
                $orderBy['likeNum'] = 'DESC';
                break;
            default:
                $orderBy['updatedTime'] = 'DESC';
                break;
        }

        return $orderBy;
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
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
