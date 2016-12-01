<?php


namespace Biz\TextActivity\Dao\Impl;


use Biz\TextActivity\Dao\TextActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TextActivityDaoImpl extends GeneralDaoImpl implements TextActivityDao
{
    protected $table = 'text_activity';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }
}