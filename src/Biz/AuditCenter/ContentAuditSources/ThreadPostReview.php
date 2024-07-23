<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Thread\Dao\ThreadDao;
use Biz\Thread\Dao\ThreadPostDao;
use Biz\Thread\Service\ThreadService;

class ThreadPostReview extends AbstractSource
{
    public function handleSource($audit)
    {
        $threadPost = $this->getThreadService()->getPost($audit['targetId']);
        if (empty($threadPost)) {
            return;
        }

        $thread = $this->getThreadService()->getThread($threadPost['threadId']);
        if (empty($thread)) {
            return;
        }

        $fields = $this->getAuditFields($audit);
        if (!empty($fields)) {
            $this->getThreadPostDao()->update($threadPost['id'], $fields);
            if ('illegal' == $fields['auditStatus']) {
                $this->getThreadDao()->update($thread['id'], ['postNum' => $thread['postNum'] - 1]);
            }
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

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->biz->dao('Thread:ThreadDao');
    }
}
