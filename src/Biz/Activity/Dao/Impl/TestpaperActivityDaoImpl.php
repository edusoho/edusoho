<?php
namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\TestpaperActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperActivityDaoImpl extends GeneralDaoImpl implements TestpaperActivityDao
{
    protected $table = 'testpaper_activity';

    public function findActivitiesByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'id = :id',
            'ids IN (:ids)'
        );

        $declares['serializes'] = array(
            'finishCondition' => 'json'
        );

        return $declares;
    }
}
