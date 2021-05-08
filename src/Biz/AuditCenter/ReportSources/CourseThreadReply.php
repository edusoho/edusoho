<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Course\Dao\ThreadPostDao;
use Biz\Course\Service\ThreadService;

class CourseThreadReply extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $threadPost = $this->getCourseThreadService()->getThreadPost($targetId);
        if (empty($threadPost)) {
            return;
        }

        return [
            'content' => $threadPost['content'],
            'author' => $threadPost['userId'],
            'createdTime' => $threadPost['createdTime'],
            'updatedTime' => $threadPost['createdTime'],
        ];
    }

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
