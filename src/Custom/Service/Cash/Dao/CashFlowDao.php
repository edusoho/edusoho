<?php

namespace Custom\Service\Cash\Dao;
use Topxia\Service\Cash\Dao\CashFlowDao as TopxiaCashFlowDao;

interface CashFlowDao extends TopxiaCashFlowDao
{

    public function sumSignAmount($conditions);
}