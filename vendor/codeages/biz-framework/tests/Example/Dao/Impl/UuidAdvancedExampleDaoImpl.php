<?php

namespace Tests\Example\Dao\Impl;

use Tests\Example\Dao\UuidExampleDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UuidAdvancedExampleDaoImpl extends AdvancedDaoImpl implements UuidExampleDao
{
    protected $table = 'example_uuid';

    public function declares()
    {
        return array(
            'id_generator' => 'uuid',
            'orderbys' => array('id'),
            'timestamps' => array('created_time', 'updated_time'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }
}
