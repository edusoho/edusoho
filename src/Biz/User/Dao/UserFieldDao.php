<?php

namespace Biz\User\Dao;

interface UserFieldDao
{
    public function getByFieldName($fieldName);

    public function getAllFieldsOrderBySeq();

    public function getAllFieldsOrderBySeqAndEnabled();
}
