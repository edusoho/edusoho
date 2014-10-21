<?php

namespace Topxia\Service\User;

interface UserFieldService
{
    public function getField($id);

    public function addUserField($field);

    public function searchFieldCount($condition);

    public function getAllFieldsOrderBySeq();

    public function getAllFieldsOrderBySeqAndEnabled();

    public function updateField($id,$fields);

    public function dropField($id);
}