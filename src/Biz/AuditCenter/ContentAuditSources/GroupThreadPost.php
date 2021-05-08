<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Group\Dao\ThreadPostDao;
use Biz\Group\Service\ThreadService;

class GroupThreadPost extends AbstractSource
{
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
