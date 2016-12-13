<?php


namespace Biz\Activity\Dao\Impl;


use Biz\Activity\Dao\FlashActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FlashActivityDaoImpl extends GeneralDaoImpl implements FlashActivityDao
{
    protected $table = 'flash_activity';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }
}