<?php
namespace Topxia\Service\Sale;

interface LinkSaleService
{

	public function getLinkSale($id);

	public function getLinkSaleBymTookeen($mTookeen);

	public function findLinkSalesByIds(array $ids);

	public function createLinkSale($linksale);

	public function searchLinkSales($conditions,$sort,$start,$limit);

	public function searchLinkSaleCount($conditions);

	

}