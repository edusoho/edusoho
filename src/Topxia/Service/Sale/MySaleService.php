<?php
namespace Topxia\Service\Sale;

interface MySaleService
{

	public function getMySale($id);

	public function getMySaleBymTookeen($mTookeen);

	public function findMySalesByIds(array $ids);

	public function createMySale($mysale);

	public function searchMySales($conditions,$sort,$start,$limit);

	public function searchMySaleCount($conditions);

	

}