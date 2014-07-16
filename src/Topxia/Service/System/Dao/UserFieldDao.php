<?php

namespace Topxia\Service\System\Dao;

interface UserFieldDao
{

    public function addField($field);

    public function getField($id);

    public function getFieldByFieldName($fieldName);

    public function searchFieldCount($condition);

    public function getAllFieldsOrderBySeq();

    public function updateField($id,$fields);

}