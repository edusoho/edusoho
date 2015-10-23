<?php
namespace Topxia\Service\Article\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Article\Dao\ArticleLikeDao;

class ArticleLikeDaoImpl extends BaseDao implements ArticleLikeDao
{
    protected $table = 'article_like';

    public function getArticleLike($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getArticleLikeByArticleIdAndUserId($articleId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? AND userId=? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($articleId, $userId)) ?: null;
    }

    public function addArticleLike($articleLike)
    {
        $affected = $this->getConnection()->insert($this->table, $articleLike);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert ArticleLike error.');
        }

        return $this->getArticleLike($this->getConnection()->lastInsertId());
    }

    public function deleteArticleLikeByArticleIdAndUserId($articleId, $userId)
    {
        return $this->getConnection()->delete($this->table, array('articleId' => $articleId, 'userId' => $userId));
    }

    public function findArticleLikesByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC";

        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function findArticleLikesByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? ORDER BY createdTime DESC";

        return $this->getConnection()->fetchAll($sql, array($articleId));
    }

    public function findArticleLikesByArticleIds(array $articleIds)
    {
        if (empty($articleIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($articleIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE articleId IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $articleIds);
    }

    public function findArticleLikesByArticleIdsAndUserId(array $articleIds, $userId)
    {
        if (empty($articleIds) || empty($userId)) {
            return array();
        }
        $marks = str_repeat('?,', count($articleIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND articleId IN ({$marks});";

        $articleIds = array_merge(array($userId), $articleIds);
        return $this->getConnection()->fetchAll($sql, $articleIds);
    }
}
