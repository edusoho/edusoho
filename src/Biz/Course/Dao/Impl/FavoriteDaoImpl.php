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
                'courseSetId = :courseSetId',
                'courseSetId IN ( :courseSetIds )',
                'courseId NOT IN ( :excludeCourseIds )',
            )
        );
    }

    public function getByUserIdAndCourseId($userId, $courseId, $type = 'course')
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId, 'type' => $type));
    }

    public function searchByUserId($userId, $start, $limit)
    {
        return $this->search(
            array(
                'userId' => $userId
            ),
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
    }

    public function countByUserId($userId)
    {
        return $this->count(array(
            'userId' => $userId
        ));
    }
}
