<?php
namespace Topxia\Service\Sale;

interface OffSaleService
{

	public function getOffSale($id);

	public function getOffSaleByCode($code);

	public function findOffSalesByIds(array $ids);

	public function createOffSale($offsale);

	public function searchOffSales($conditions,$sort,$start,$limit);

	public function searchOffSaleCount($conditions);

	

}