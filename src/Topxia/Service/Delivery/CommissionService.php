<?php
namespace Topxia\Service\Delivery;

interface CommissionService
{

	public function getCommission($id);

	public function findCommissionsByIds(array $ids);

	public function createCommission($commission);

	public function searchCommissions($conditions,$sort,$start,$limit);

	public function searchCommissionCount($conditions);

	

}