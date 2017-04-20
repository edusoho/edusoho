<?php

namespace Biz\Activity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Activity\Dao\VideoActivityDao;

class VideoActivityDaoImpl extends GeneralDaoImpl implements VideoActivityDao
{
    protected $table = 'activity_video';

    public function declares()
    {
        return array(
            'serializes' => array('media' => 'json'),
        );
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }
}
