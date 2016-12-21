<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\FavoriteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FavoriteDaoImpl extends GeneralDaoImpl implements FavoriteDao
{
    protected $table = 'course_favorite';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys'   => array('replayId', 'createdTime'),
            'conditions' => array(
                'courseId = :courseId',
                'userId = :userId',
                'type = :type',
                'createdTime >= :createdTime_GE',
                'courseId NOT IN ( :excludeCourseIds )',
            )
        );
    }

    public function getByUserIdAndCourseId($userId, $courseId, $type = 'course')
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId, 'type' => $type));
    }

    public function findByUserId($userId, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND type = 'course' ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, array($userId)) ?: array();
    }

    public function countByUserId($userId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND type = 'course'";
        return $this->db()->fetchColumn($sql, array($userId));
    }
}
