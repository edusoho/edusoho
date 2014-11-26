<?php 
namespace Topxia\Service\EssayContent\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\EssayContent\Dao\EssayContentDao;

class EssayContentDaoImpl extends BaseDao implements EssayContentDao
{
    protected $table = 'essay_relation';

    public function findContentsByArticleId($articleId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE articleId = ? ORDER BY createdTime ASC";
        return $this->getConnection()->fetchAll($sql, array($articleId));
    }
}