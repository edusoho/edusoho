<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Course\Service\ThreadService;

class CourseThreadReply extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $threadPost = $this->getCourseThreadService()->getThreadPost($targetId);

        return [
            'content' => $threadPost['content'],
            'author' => $threadPost['userId'],
            'createdTime' => $threadPost['createdTime'],
        ];
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->biz->service('Course:ThreadService');
    }
}
