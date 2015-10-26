<?php

namespace Topxia\Service\Card\Dao;

interface CardDao
{

	public function addCard($card);

	public function getCard($id);

	public function findCardsByUserIdAndCardType($userId,$cardType);

	 
}