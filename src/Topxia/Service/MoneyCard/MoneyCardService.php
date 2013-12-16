<?php
namespace Topxia\Service\MoneyCard;

interface MoneyCardService
{
	public function getMoneyCard ($id);

    public function getBatch ($id);

	public function searchMoneyCards (array $conditions, array $oderBy, $start, $limit);

    public function searchMoneyCardsCount(array $conditions);

    public function searchBatchs(array $conditions, array $oderBy, $start, $limit);

    public function searchBatchsCount(array $conditions);

    public function createMoneyCard (array $moneyCardData);

    public function lockMoneyCard ($id);

    public function unlockMoneyCard ($id);

    public function deleteMoneyCard ($id);

    public function lockBatch ($id);

    public function unlockBatch ($id);

    public function deleteBatch ($id);

}