<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseNoteException;
use Biz\Course\Service\CourseNoteService;

class CourseNoteLike extends AbstractResource
{
    public function add(ApiRequest $request, $courseId, $noteId)
    {
        $note = $this->getNoteService()->getNote($noteId);

        if (empty($note)) {
            CourseNoteException::NOTFOUND_NOTE();
        }

        return ['success' => $this->getNoteService()->like($noteId)];
    }

    public function remove(ApiRequest $request, $courseId, $noteId)
    {
        $note = $this->getNoteService()->getNote($noteId);

        if (empty($note)) {
            CourseNoteException::NOTFOUND_NOTE();
        }

        return ['success' => $this->getNoteService()->cancelLike($noteId)];
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->service('Course:CourseNoteService');
    }
}
