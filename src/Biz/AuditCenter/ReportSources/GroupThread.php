<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Group\Dao\ThreadDao;
use Biz\Group\Service\ThreadService;

class GroupThread extends AbstractSource
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
            'updatedTime' => $thread['updatedTime'],
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
            $this->getGroupThreadDao()->update($thread['id'], $fields);
        }
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->biz->service('Group:ThreadService');
    }

    /**
     * @return ThreadDao
     */
    protected function getGroupThreadDao()
    {
        return $this->biz->dao('Group:ThreadDao');
    }
}
