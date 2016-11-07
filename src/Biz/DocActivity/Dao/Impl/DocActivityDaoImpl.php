<?php


namespace Biz\DocActivity\Dao\Impl;


use Biz\DocActivity\Dao\DocActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DocActivityDaoImpl extends GeneralDaoImpl implements DocActivityDao
{
    protected $table = 'doc_activity';

    public function declares()
    {

    }
}