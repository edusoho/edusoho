<?php 
namespace Topxia\Service\EssayContent\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\EssayContent\Dao\EssayChapterDao;

class EssayChapterDaoImpl extends BaseDao implements EssayChapterDao
{
    protected $table = 'essay_chapter';

    public function findChaptersByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? ORDER BY createdTime ASC";
        return $this->getConnection()->fetchAll($sql, array($articleId));
    }
}