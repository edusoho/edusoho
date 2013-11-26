<?php
namespace Topxia\Service\User\Dao;

interface UserApprovalDao
{
	function getApproval($id);

	function addApproval($approval);

	function getApprovalByUserId($userId);
}