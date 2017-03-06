<?php

namespace Biz\Article\Dao\Impl;

use Biz\Article\Dao\ArticleLikeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ArticleLikeDaoImpl extends GeneralDaoImpl implements ArticleLikeDao
{
    protected $table = 'article_like';

    public function getByArticleIdAndUserId($articleId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? AND userId=? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($articleId, $userId)) ?: null;
    }

    public function deleteByArticleIdAndUserId($articleId, $userId)
    {
        return $this->db()->delete($this->table, array('articleId' => $articleId, 'userId' => $userId));
    }

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array($userId));
    }

    public function findByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array($articleId));
    }

    public function findByArticleIds(array $articleIds)
    {
        return $this->findInField('articleId', $articleIds);
    }

    public function findByArticleIdsAndUserId(array $articleIds, $userId)
    {
        if (empty($articleIds) || empty($userId)) {
            return array();
        }

        $marks = str_repeat('?,', count($articleIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND articleId IN ({$marks});";
        $articleIds = array_merge(array($userId), $articleIds);

        return $this->db()->fetchAll($sql, $articleIds);
    }

    public function declares()
    {
        return array();
    }
}
