<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Thread\Dao\ThreadDao;
use Biz\Thread\Service\ThreadService;

class Thread extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $thread = $this->getThreadService()->getThread($targetId);
        if (empty($thread)) {
            return;
        }

        return [
            'content' => $thread['content'],
            'author' => $thread['userId'],
            'createdTime' => $thread['createdTime'],
            'updatedTime' => $thread['updateTime'],
        ];
    }

    public function handleSource($audit)
    {
        $thread = $this->getThreadService()->getThread($audit['targetId']);
        if (empty($thread)) {
            return;
        }

        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getThreadDao()->update($thread['id'], $fields);
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
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->biz->dao('Thread:ThreadDao');
    }
}
