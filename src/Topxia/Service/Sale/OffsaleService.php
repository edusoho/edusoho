<?php
namespace Topxia\Service\Sale;

interface OffsaleService
{

	public function getOffsale($id);

	public function getOffsaleByCode($code);

	public function findOffsalesByIds(array $ids);

	public function createOffsale($offsale);

	public function searchOffsales($conditions,$sort,$start,$limit);

	public function searchOffsaleCount($conditions);

	

}