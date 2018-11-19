<?php

namespace Biz\SearchKeyword\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\SearchKeyword\Dao\SearchKeywordDao;

class SearchKeywordDaoImpl extends GeneralDaoImpl implements SearchKeywordDao
{
    protected $table = 'search_keyword';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'name = :name',
                'name LIKE :likeName',
            ),
        );
    }
}
