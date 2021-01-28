<?php

namespace Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Tests\Example\Dao\AdvancedExampleDao;

class AdvancedExample2DaoImpl extends AdvancedDaoImpl implements AdvancedExampleDao
{
    protected $table = 'example';

    public function declares()
    {
        return array(
        );
    }
}
