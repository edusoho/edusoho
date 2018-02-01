<?php

namespace testpaper\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class testpaper_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_testpaper';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByMediaIds($mediaIds)
    {
        return $this->findInField('mediaId', $mediaIds);
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'id = :id',
            'id IN (:ids)',
        );

        $declares['serializes'] = array(
            'finishCondition' => 'json',
        );

        return $declares;
    }
}