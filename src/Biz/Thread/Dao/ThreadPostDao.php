<?php

namespace Biz\Thread\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadPostDao extends GeneralDaoInterface
{
    public function deletePostsByThreadId($threadId);

    public function deletePostsByParentId($parentId);

    public function findThreadIds($conditions);
}
