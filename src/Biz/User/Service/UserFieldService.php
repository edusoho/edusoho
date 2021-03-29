<?php

namespace Biz\User\Service;

interface UserFieldService
{
    public function getField($id);

    public function addUserField($field);

    public function countFields($condition);

    public function getFieldsOrderBySeq();

    public function getEnabledFieldsOrderBySeq();

    public function updateField($id, $fields);

    public function dropField($id);
}
