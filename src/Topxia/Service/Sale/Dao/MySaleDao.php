<?php

namespace Topxia\Service\Sale\Dao;

interface MySaleDao
{
	const TABLENAME = 'mysale';

    public function getMySale($id);

    public function findMySalesByIds(array $ids);

    public function searchMySales($conditions, $orderBy, $start, $limit);

    public function searchMySaleCount($conditions);

    public function addMySale($mysale);

    public function updateMySale($id, $mysale);

    public function deleteMySale($id);

   
}