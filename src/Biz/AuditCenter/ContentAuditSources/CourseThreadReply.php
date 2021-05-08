<?php

namespace Biz\AuditCenter\ContentAuditSources;

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
        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getCourseThreadPostDao()->update($threadPost['id'], $fields);
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
}
