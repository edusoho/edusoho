<?php

namespace Topxia\Service\Card\Dao;

interface CardDao
{

	public function addCard($card);

	public function getCard($id);

	public function updateCard($cardId,$cardType,$fields);

	public function getCardByCardIdAndCardType($cardId,$cardType);

	public function findCardsByUserIdAndCardType($userId,$cardType);

	 
}