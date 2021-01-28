<?php

namespace Biz\Article\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ArticleLikeDao extends GeneralDaoInterface
{
    public function getByArticleIdAndUserId($articleId, $userId);

    public function deleteByArticleIdAndUserId($articleId, $userId);

    public function findByUserId($userId);

    public function findByArticleId($articleId);

    public function findByArticleIds(array $articleIds);

    public function findByArticleIdsAndUserId(array $articleIds, $userId);
}
