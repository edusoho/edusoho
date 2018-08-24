<?php

namespace download\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class download_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_download';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('fileIds' => 'json'),
        );
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}