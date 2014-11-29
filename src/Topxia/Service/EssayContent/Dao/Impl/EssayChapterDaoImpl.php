<?php 
namespace Topxia\Service\EssayContent\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\EssayContent\Dao\EssayChapterDao;

class EssayChapterDaoImpl extends BaseDao implements EssayChapterDao
{
    protected $table = 'essay_chapter';

    public function getChapter($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findChaptersByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? ORDER BY createdTime ASC";
        return $this->getConnection()->fetchAll($sql, array($articleId));
    }

    public function getChapterMaxSeqByArticleId($articleId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table} WHERE  articleId = ?";
        return $this->getConnection()->fetchColumn($sql, array($articleId));
    }

    public function getChapterCountByArticleIdAndType($articleId, $type)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  articleId = ? AND type = ?";
        return $this->getConnection()->fetchColumn($sql, array($articleId, $type));
    }

    public function getChapterCountByArticleIdAndTypeAndParentId($articleId, $type, $parentId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  articleId = ? AND type = ? AND parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($articleId, $type, $parentId));
    }

    public function getLastChapterByArticleIdAndType($articleId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  articleId = ? AND type = ? ORDER BY seq DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($articleId, $type)) ? : null;
    }

    public function getLastChapterByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  articleId = ? ORDER BY seq DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($articleId)) ? : null;
    }

    public function addChapter(array $chapter)
    {
        $affected = $this->getConnection()->insert($this->table, $chapter);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course chapter error.');
        }
        return $this->getChapter($this->getConnection()->lastInsertId());
    }

    public function updateChapter($id, array $chapter)
    {
        $this->getConnection()->update($this->table, $chapter, array('id' => $id));
        return $this->getChapter($id);
    }

    public function deleteChapter($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
}