<?php
namespace Biz\TestpaperActivity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\TestpaperActivity\Dao\TestpaperActivityDao;

class TestpaperActivityDaoImpl extends GeneralDaoImpl implements TestpaperActivityDao
{
    protected $table = 'testpaper_activity';

    public function declares()
    {
        $declares['conditions'] = array(
            'id = :id'
        );

        $declares['serializes'] = array(
            'finishCondition' => 'json'
        );

        return $declares;
    }
}
