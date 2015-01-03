<?php
namespace Topxia\Service\Cash;

interface CashService
{
    public function createAccount($userId);

    public function searchFlows($conditions, $orderBy, $start, $limit);

    public function searchFlowsCount($conditions);

    public function outflow($userId, $flow);

    public function inflow($userId, $flow);

    public function searchAccount($conditions, $orderBy, $start, $limit);

    public function searchAccountCount($conditions);

    public function getAccount($id);

    public function getChangeByUserId($userId);

    public function addChange($userId);

    public function changeCoin($amount,$account,$userId);

    public function reward($amount,$name,$userId,$type=null);

}