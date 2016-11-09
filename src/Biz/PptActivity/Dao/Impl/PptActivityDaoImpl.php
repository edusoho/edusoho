<?php


namespace Biz\PptActivity\Dao\Impl;


use Biz\PptActivity\Dao\PptActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PptActivityDaoImpl extends GeneralDaoImpl implements PptActivityDao
{
    protected $table = 'ppt_activity';

    public function declares()
    {

    }
}