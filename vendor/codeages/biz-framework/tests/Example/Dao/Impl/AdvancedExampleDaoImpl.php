<?php

namespace Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Tests\Example\Dao\AdvancedExampleDao;

class AdvancedExampleDaoImpl extends AdvancedDaoImpl implements AdvancedExampleDao
{
    protected $table = 'example';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'ids1' => 'json',
                'ids2' => 'delimiter',
                'null_value' => 'json',
                'php_serialize_value' => 'php',
                'json_serialize_value' => 'json',
                'delimiter_serialize_value' => 'delimiter',
            ),
            'orderbys' => array('name', 'created_time'),
            'conditions' => array(
                'name = :name',
                'name pre_LIKE :pre_like',
                'name suF_like :suf_name',
                'name LIKE :like_name',
                'id iN (:ids)',
                'ids1 = :ids1',
            ),
            'wave_cahceable_fields' => array(
            ),
        );
    }
}
