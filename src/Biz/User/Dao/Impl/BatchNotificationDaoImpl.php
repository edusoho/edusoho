<?php

namespace Biz\User\Service\Dao\Impl;

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

    public function delete($id)
    {
        $result = parent::delete($id);
        $this->clearCached();
        return $result;
    }

    public function update($id, $fields)
    {
        $bn = parent::update($id, $fields);
        $this->clearCached();
        return $bn;
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        if (empty($orderBy)) {
            $orderBy = array(
                'createdTime' => 'DESC'
            );
        }

        return parent::search($conditions, $orderBy, $start, $limit);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'content' => 'json'
            )
        );
    }
}
