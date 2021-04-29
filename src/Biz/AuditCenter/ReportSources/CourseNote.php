<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Course\Dao\CourseNoteDao;
use Biz\Course\Service\CourseNoteService;

class CourseNote extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $note = $this->getCourseNoteService()->getNote($targetId);
        if (empty($note)) {
            return;
        }

        return [
            'content' => $note['content'],
            'author' => $note['userId'],
            'createdTime' => $note['createdTime'],
            'updatedTime' => $note['updatedTime'],
        ];
    }

    public function handleSource($audit)
    {
        $note = $this->getCourseNoteService()->getNote($audit['targetId']);
        if (empty($note)) {
            return;
        }

        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getCourseNoteDao()->update($note['id'], $fields);
        }
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->biz->service('Course:CourseNoteService');
    }

    /**
     * @return CourseNoteDao
     */
    protected function getCourseNoteDao()
    {
        return $this->biz->dao('Course:CourseNoteDao');
    }
}
