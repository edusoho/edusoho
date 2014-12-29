<?php
namespace Topxia\Service\Cash;

interface CashAccountService
{
	public function createAccount($userId);
    
    public function getAccount($id);

    public function getAccountByUserId($userId, $lock=false);

	public function searchAccount($conditions, $orderBy, $start, $limit);

    public function searchAccountCount($conditions);

    public function getChangeByUserId($userId);

    public function addChange($userId);

    public function changeCoin($amount,$account,$userId);

    public function reward($amount,$name,$userId,$type=null);

    public function waveCashField($id, $value);

    public function waveDownCashField($id, $value);
}
