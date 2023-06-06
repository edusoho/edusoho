<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Course\Dao\ThreadDao;
use Biz\Course\Dao\ThreadPostDao;
use Biz\Course\Service\ThreadService;

class CourseThreadReply extends AbstractSource
{
    public function handleSource($audit)
    {
        $threadPost = $this->getCourseThreadService()->getThreadPost($audit['targetId']);
        if (empty($threadPost)) {
            return;
        }
        $thread = $this->getCourseThreadService()->getThreadByThreadId($threadPost['threadId']);
        if (empty($thread)) {
            return;
        }

        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getCourseThreadPostDao()->update($threadPost['id'], $fields);
            if ('illegal' == $fields['auditStatus']) {
                $this->getCourseThreadDao()->update($thread['id'], ['postNum' => $thread['postNum'] - 1]);
            }
        }
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->biz->service('Course:ThreadService');
    }

    /**
     * @return ThreadPostDao
     */
    protected function getCourseThreadPostDao()
    {
        return $this->biz->dao('Course:ThreadPostDao');
    }

    /**
     * @return ThreadDao
     */
    protected function getCourseThreadDao()
    {
        return $this->biz->dao('Course:ThreadDao');
    }
}
