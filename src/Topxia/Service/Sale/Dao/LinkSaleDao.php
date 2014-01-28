<?php

namespace Topxia\Service\Sale\Dao;

interface LinkSaleDao
{
	const TABLENAME = 'sale_linksale';

    public function getLinkSale($id);

    public function findLinkSalesByIds(array $ids);

    public function searchLinkSales($conditions, $orderBy, $start, $limit);

    public function searchLinkSaleCount($conditions);

    public function addLinkSale($linksale);

    public function updateLinkSale($id, $linksale);

    public function deleteLinkSale($id);

   
}