<?php
namespace Custom\Service\Cash;
use Topxia\Service\Cash\CashService as TopxiaCashService;

interface CashService extends TopxiaCashService
{
    public function createAccount($userId);

    public function inflow($userId, $flow);

    public function searchAccount($conditions, $orderBy, $start, $limit);

    public function searchAccountCount($conditions);

    public function getAccount($id);

    public function getChangeByUserId($userId);

    public function addChange($userId);

    public function changeCoin($amount,$account,$userId);

    public function reWard($amount,$name,$userId,$type=null);

    public function sumSignAmount($conditions);

}