<?php

namespace Biz\SearchKeyword\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\SearchKeyword\Dao\SearchKeywordDao;

class SearchKeywordDaoImpl extends GeneralDaoImpl implements SearchKeywordDao
{
    protected $table = 'search_keyword';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updateTime'),
            'orderbys' => array('id', 'times', 'createdTime', 'updateTime'),
            'conditions' => array(
                'name = :name',
                'name LIKE :likeName',
            ),
        );
    }
}
