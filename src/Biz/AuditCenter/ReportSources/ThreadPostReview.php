<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Thread\Dao\ThreadPostDao;
use Biz\Thread\Service\ThreadService;

class ThreadPostReview extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $thread = $this->getThreadService()->getPost($targetId);
        if (empty($thread)) {
            return;
        }

        return [
            'content' => $thread['content'],
            'author' => $thread['userId'],
            'createdTime' => $thread['createdTime'],
            'updatedTime' => $thread['createdTime'],
        ];
    }

    public function handleSource($audit)
    {
        $thread = $this->getThreadService()->getPost($audit['targetId']);

        if (empty($thread)) {
            return;
        }

        $fields = $this->getAuditFields($audit);
        if (!empty($fields)) {
            $this->getThreadPostDao()->update($thread['id'], $fields);
        }
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->biz->service('Thread:ThreadService');
    }

    /**
     * @return ThreadPostDao
     */
    protected function getThreadPostDao()
    {
        return $this->biz->dao('Thread:ThreadPostDao');
    }
}
