<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Thread\Service\ThreadService;

class ThreadPostReview extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $thread = $this->getThreadService()->getPost($targetId);

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
