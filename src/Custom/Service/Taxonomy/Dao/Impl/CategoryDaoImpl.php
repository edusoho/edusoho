<?php

namespace Custom\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Taxonomy\Dao\CategoryDao;

class CategoryDaoImpl extends BaseDao implements CategoryDao
{
    protected $table = 'category';


 public function updateCategoryIsSearch($id, $isSearch){
      $this->getConnection()->update($this->table, $isSearch, array('id' => $id));
        return $id;
 }

}