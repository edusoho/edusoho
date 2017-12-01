<?php

namespace Biz\Xapi\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class StatementArchiveDaoImpl extends AdvancedDaoImpl
{
    protected $table = 'xapi_statement_archive';

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json',
            ),
            'timestamps' => array(
            ),
            'orderbys' => array('created_time', 'push_time'),
            'conditions' => array(),
        );
    }
}
