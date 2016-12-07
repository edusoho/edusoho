<?php


namespace Biz\FlashActivity\Dao\Impl;


use Biz\FlashActivity\Dao\FlashActivityDao;
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