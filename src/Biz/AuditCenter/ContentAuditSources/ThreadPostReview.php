<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Thread\Dao\ThreadPostDao;
use Biz\Thread\Service\ThreadService;

class ThreadPostReview extends AbstractSource
{
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
