<?php

namespace Topxia\Service\Sale\Dao;

interface OffSaleDao
{
	const TABLENAME = 'sale_offsale';

    public function getOffSale($id);

    public function findOffSalesByIds(array $ids);

    public function searchOffSales($conditions, $orderBy, $start, $limit);

    public function searchOffSaleCount($conditions);

    public function addOffSale($member);

    public function updateOffSale($id, $member);

    public function deleteOffSale($id);

   
}