<?php
namespace Topxia\Service\Sale;

interface CommissionService
{

	public function getCommission($id);

	public function findCommissionsByIds(array $ids);

	public function createCommission($commission);

	public function searchCommissions($conditions,$sort,$start,$limit);

	public function searchCommissionCount($conditions);

	

}