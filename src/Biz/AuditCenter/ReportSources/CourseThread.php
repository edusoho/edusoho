<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Course\Dao\ThreadDao;
use Biz\Course\Service\ThreadService;
use Biz\Course\ThreadException;

class CourseThread extends AbstractSource
{
    public function getReportContext($targetId)
    {
        try {
            $thread = $this->getCourseThreadService()->getThreadByThreadId($targetId);
            if (empty($thread)) {
                return;
            }

            return [
                'content' => $thread['content'],
                'author' => $thread['userId'],
                'createdTime' => $thread['createdTime'],
                'updatedTime' => $thread['updatedTime'],
            ];
        } catch (ThreadException $e) {
            return;
        }
    }

    public function handleSource($audit)
    {
        $thread = $this->getCourseThreadService()->getThreadByThreadId($audit['targetId']);
        if (empty($thread)) {
            return;
        }

        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getCourseThreadDao()->update($thread['id'], $fields);
        }
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return  $this->biz->service('Course:ThreadService');
    }

    /**
     * @return ThreadDao
     */
    protected function getCourseThreadDao()
    {
        return $this->biz->dao('Course:ThreadDao');
    }
}
