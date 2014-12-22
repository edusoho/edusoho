<?php

namespace Custom\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Taxonomy\Dao\TagDao;

class TagDaoImpl extends BaseDao implements TagDao
{
    protected $table = 'tag';



    public function findAllTags($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY weight,createdTime ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

   

}