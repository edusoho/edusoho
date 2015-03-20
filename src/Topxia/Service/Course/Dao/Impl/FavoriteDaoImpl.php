<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\FavoriteDao;

class FavoriteDaoImpl extends BaseDao implements FavoriteDao
{
    protected $table = 'course_favorite';

    public function getFavorite($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getFavoriteByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $courseId)) ? : null; 
    }

    public function findCourseFavoritesByUserId($userId, $start, $limit)
    {
        
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId)) ? : array();
    }

    public function getFavoriteCourseCountByUserId($userId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId));
    }

    public function addFavorite($favorite)
    {
        $affected = $this->getConnection()->insert($this->table, $favorite);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course favorite error.');
        }
        return $this->getFavorite($this->getConnection()->lastInsertId());
    }

    public function deleteFavorite($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}