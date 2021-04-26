<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Course\Service\ThreadService;

class CourseThread extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $thread = $this->getCourseThreadService()->getThreadByThreadId($targetId);

        return [
            'content' => $thread['content'],
            'author' => $thread['userId'],
            'createdTime' => $thread['createdTime'],
        ];
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return  $this->biz->service('Course:ThreadService');
    }
}
