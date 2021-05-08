<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Course\Dao\CourseNoteDao;
use Biz\Course\Service\CourseNoteService;

class CourseNote extends AbstractSource
{
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
