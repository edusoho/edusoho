<?php
namespace Topxia\Service\User\Dao;


interface CreditLogDao
{
	public function getCreditLog($id);

	public function addCreditLog($CreditLog);
}