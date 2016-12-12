<?php

namespace Biz\Activity\Type\Audio\Dao\Impl;


use Biz\Activity\Type\Audio\Dao\AudioActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AudioActivityDaoImpl extends GeneralDaoImpl implements AudioActivityDao
{
    protected $table = 'audio_activity';

    public function declares()
    {
        return array(
            'serializes' => array('media' => 'json'),
        );
    }
}