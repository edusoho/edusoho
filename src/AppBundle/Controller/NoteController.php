<?php


namespace AppBundle\Controller;


use Biz\Course\Service\CourseService;
use Biz\Note\Service\CourseNoteService;
use Symfony\Component\HttpFoundation\Request;

class NoteController extends BaseController
{
    public function createCourseNoteAction(Request $request, $courseId, $taskId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        if ($request->isMethod('POST')) {
            $note             = $request->request->all();
            $note['courseId'] = $courseId;
            $note['taskId'] = $taskId;
            $note['status'] = isset($note['status']) && $note['status'] === 'on' ? 1 : 0;
            $note           = $this->getNoteService()->saveNote($note);
            return $this->createJsonResponse($note);
        }
    }

    public function likeAction(Request $request, $id)
    {
        $note = $this->getNoteService()->getNote($id);

        if(empty($note)){
            throw $this->createNotFoundException('not found');
        }

        return $this->createJsonResponse($this->getNoteService()->like($id));
    }

    public function cancelLikeAction(Request $request, $id)
    {
        $note = $this->getNoteService()->getNote($id);

        if(empty($note)){
            throw $this->createNotFoundException('not found');
        }

        return $this->createJsonResponse($this->getNoteService()->cancelLike($id));
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Note:CourseNoteService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
