<?php

namespace Biz\Thread\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadPostDao extends GeneralDaoInterface
{
    public function getPostPostionInArticle($articleId, $postId);

    public function deletePostsByThreadId($threadId);

    public function deletePostsByParentId($parentId);
}
