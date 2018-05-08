<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\BatchNotificationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class BatchNotificationDaoImpl extends GeneralDaoImpl implements BatchNotificationDao
{
    protected $table = 'batch_notification';

    public function count($conditions)
    {
        if (isset($conditions['content'])) {
            if (empty($conditions['content'])) {
                unset($conditions['content']);
            } else {
                $conditions['content'] = "%{$conditions['content']}%";
            }
        }

        return parent::count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit, $columns = array())
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        if (empty($orderBy)) {
            $orderBy = array(
                'createdTime' => 'DESC',
            );
        }

        return parent::search($conditions, $orderBy, $start, $limit);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'content' => 'json',
            ),
            'conditions' => array(
                'id NOT in ( :excludeIds )',
                'id = :id',
                'published = :published',
                'createdTime = :createdTime',
                'sendedTime <= :sendedTime_LE',
                'sendedTime >= :sendedTime_GE',
            ),
            'orderbys' => array('createdTime'),
        );
    }
}
