<?php

namespace audio\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class audio_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_audio';

    public function declares()
    {
        return array(
            'serializes' => array('media' => 'json'),
        );
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
