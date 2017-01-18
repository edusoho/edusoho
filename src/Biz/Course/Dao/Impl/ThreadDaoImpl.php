<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\ThreadDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadDaoImpl extends GeneralDaoImpl implements ThreadDao
{
    protected $table = 'course_thread';

    protected function _createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        return parent::_createQueryBuilder($conditions);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys'   => array('isStick', 'latestPostTime', 'createdTime', 'latestPostTime', 'hitNum'),
            'conditions' => array(
                'updatedTime >= :updatedTime_GE',
                'courseId = :courseId',
                'courseSetId = :courseSetId',
                'courseSetId IN (:courseSetIds) ',
                'taskId = :taskId',
                'userId = :userId',
                'type = :type',
                'type IN (:types)',
                'isStick = :isStick',
                'isElite = :isElite',
                'postNum = :postNum',
                'postNum > :postNumLargerThan',
                'title LIKE :title',
                'content LIKE :content',
                'courseId IN (:courseIds)',
                'private = :private',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
                'createdTime >= :startCreatedTime',
                'createdTime < :endCreatedTime'
            )
        );
    }
}
