<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\AudioActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AudioActivityDaoImpl extends GeneralDaoImpl implements AudioActivityDao
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
