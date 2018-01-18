<?php

namespace video\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class video_activity_dao extends GeneralDaoImpl
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
