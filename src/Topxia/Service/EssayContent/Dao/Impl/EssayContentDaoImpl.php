<?php 
namespace Topxia\Service\EssayContent\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\EssayContent\Dao\EssayContentDao;

class EssayContentDaoImpl extends BaseDao implements EssayContentDao
{
    protected $table = 'essay_relation';

    public function getContent($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findContentsByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? ORDER BY createdTime ASC";
        return $this->getConnection()->fetchAll($sql, array($articleId));
    }

    public function findContentsByChapterId($chapterId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE chapterId = ? ORDER BY createdTime ASC";
        return $this->getConnection()->fetchAll($sql, array($chapterId));
    }
    
    public function getContentMaxSeqByArticleId($articleId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table} WHERE  articleId = ?";
        return $this->getConnection()->fetchColumn($sql, array($articleId));
    }

    public function updateContent($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getContent($id);
    }

    public function getContentCountByArticleId($articleId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE articleId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($articleId));
    }

    public function addContent($content)
    {
        $affected = $this->getConnection()->insert($this->table, $content);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert essay content error.');
        }
        return $this->getContent($this->getConnection()->lastInsertId());
    }

    public function deleteContent($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}