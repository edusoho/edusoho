<?php

namespace Topxia\Service\MyGroup\Dao;

interface ThreadDao
{
	public function addThread($info);
    public function searchThread($id,$strat,$limit,$sort);
}