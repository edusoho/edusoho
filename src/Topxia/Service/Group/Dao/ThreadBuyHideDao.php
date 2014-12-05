<?php

namespace Topxia\Service\Group\Dao;

interface ThreadBuyHideDao
{
    public function getBuyHide($id);

    public function addBuyHide($fields);
    
    public function getbuyHideByUserIdandThreadId($id,$userId);

    public function getbuyHideByUserIdandHideId($userId,$hideId);
}