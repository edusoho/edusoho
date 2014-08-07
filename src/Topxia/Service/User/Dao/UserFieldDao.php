<?php

namespace Topxia\Service\User\Dao;

interface UserFieldDao
{

    public function addField($field);

    public function getField($id);

    public function getFieldByFieldName($fieldName);

    public function searchFieldCount($condition);

    public function getAllFieldsOrderBySeq();
    
    public function getAllFieldsOrderBySeqAndEnabled();

    public function updateField($id,$fields);

    public function deleteField($id);
}