<?php

namespace Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Tests\Example\Dao\UuidExampleDao;

class UuidExampleDaoImpl extends GeneralDaoImpl implements UuidExampleDao
{
    protected $table = 'example_uuid';

    public function declares()
    {
        return array(
            'id_generator' => 'uuid',
            'timestamps' => array('created_time', 'updated_time'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }
}
