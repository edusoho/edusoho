<?php

namespace Topxia\Service\Sale\Dao;

interface CommissionDao
{
	const TABLENAME = 'sale_commission';

    public function getCommission($id);

    public function findCommissionsByIds(array $ids);

    public function searchCommissions($conditions, $orderBy, $start, $limit);

    public function searchCommissionCount($conditions);

    public function addCommission($commission);

    public function updateCommission($id, $commission);

    public function deleteCommission($id);

   
}