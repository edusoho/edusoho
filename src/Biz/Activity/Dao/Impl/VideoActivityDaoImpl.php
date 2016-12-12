<?php

namespace Biz\Activity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Activity\Dao\VideoActivityDao;

class VideoActivityDaoImpl extends GeneralDaoImpl implements VideoActivityDao
{
    protected $table = 'video_activity';

    public function declares()
    {
        return array(
            'serializes' => array('media' => 'json'),
        );
    }
}