<?php

namespace Topxia\Service\Sale\Dao;

interface OffSaleDao
{
	const TABLENAME = 'offsale';

    public function getOffsale($id);

    public function findOffsalesByIds(array $ids);

    public function searchOffsales($conditions, $orderBy, $start, $limit);

    public function searchOffsaleCount($conditions);

    public function addOffsale($member);

    public function updateOffsale($id, $member);

    public function deleteOffsale($id);

   
}