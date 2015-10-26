<?php

namespace Topxia\Service\Card\CardDetailProcessor;

interface CardDetailProcessor
{

	public function getCardDetailByCardId($id);

	public function getCardsDetailByCardIds($ids,$orderBy,$start,$limit);

}
