<?php

namespace Topxia\Service\System;

interface SettingService
{
    public function set($name, $value);

    public function get($name, $default = NULL);

    public function delete ($name);

    public function getField($id);

    public function addUserField($field);

    public function searchFieldCount($condition);

    public function getAllFieldsOrderBySeq();

    public function updateField($id,$fields);
}