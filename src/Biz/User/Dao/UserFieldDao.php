<?php

namespace Biz\User\Dao;

interface UserFieldDao
{
    public function getByFieldName($fieldName);

    public function getFieldsOrderBySeq();

    public function getEnabledFieldsOrderBySeq();
}
