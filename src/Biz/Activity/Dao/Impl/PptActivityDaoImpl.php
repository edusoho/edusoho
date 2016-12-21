<?php


namespace Biz\Activity\Dao\Impl;


use Biz\Activity\Dao\PptActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PptActivityDaoImpl extends GeneralDaoImpl implements PptActivityDao
{
    protected $table = 'ppt_activity';

    public function declares()
    {

    }
}