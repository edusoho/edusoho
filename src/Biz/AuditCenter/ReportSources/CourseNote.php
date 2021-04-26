<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Course\Service\CourseNoteService;

class CourseNote extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $note = $this->getCourseNoteService()->getNote($targetId);

        return [
            'content' => $note['content'],
            'author' => $note['userId'],
            'createdTime' => $note['createdTime'],
        ];
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->biz->service('Course:CourseNoteService');
    }
}
