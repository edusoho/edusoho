<?php


namespace Biz\Activity\Dao\Impl;


use Biz\Activity\Dao\DocActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DocActivityDaoImpl extends GeneralDaoImpl implements DocActivityDao
{
    protected $table = 'doc_activity';

    public function declares()
    {

    }
}