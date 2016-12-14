<?php

namespace Biz\User\Service;

interface UserFieldService
{
    public function getField($id);

    public function addUserField($field);

    public function countFields($condition);

    public function getAllFieldsOrderBySeq();

    public function getAllFieldsOrderBySeqAndEnabled();

    public function updateField($id, $fields);

    public function dropField($id);
}
