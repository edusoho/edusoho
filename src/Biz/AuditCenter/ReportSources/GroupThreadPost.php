<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Group\Dao\ThreadPostDao;
use Biz\Group\Service\ThreadService;

class GroupThreadPost extends AbstractSource
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
            $this->getGroupThreadPostDao()->update($thread['id'], $fields);
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
     * @return ThreadPostDao
     */
    protected function getGroupThreadPostDao()
    {
        return $this->biz->dao('Group:ThreadPostDao');
    }
}
