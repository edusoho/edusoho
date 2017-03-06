<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\CommentDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CommentDaoImpl extends GeneralDaoImpl implements CommentDao
{
    protected $table = 'comment';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
            ),
            'conditions' => array(
                'objectType = :objectType',
                'objectId = :objectId',
            ),
        );
    }

    public function findByObjectTypeAndObjectId($objectType, $objectId, $start, $limit)
    {
        return $this->search(
            array(
                'objectType' => $objectType,
                'objectId' => $objectId,
            ),
            array(
                'createdTime' => 'DESC',
            ),
            $start,
            $limit
        );
    }

    public function findByObjectType($objectType, $start, $limit)
    {
        return $this->search(
            array(
                'objectType' => $objectType,
            ),
            array(
                'createdTime' => 'DESC',
            ),
            $start,
            $limit
        );
    }

    public function countByObjectType($objectType)
    {
        return $this->count(array(
            'objectType' => $objectType,
        ));
    }
}
