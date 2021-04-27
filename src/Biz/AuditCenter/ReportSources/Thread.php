<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Thread\Service\ThreadService;

class Thread extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $thread = $this->getThreadService()->getThread($targetId);

        return [
            'content' => $thread['content'],
            'author' => $thread['userId'],
            'createdTime' => $thread['createdTime'],
        ];
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->biz->service('Thread:ThreadService');
    }
}
