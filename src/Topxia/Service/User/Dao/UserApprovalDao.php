<?php
namespace Topxia\Service\User\Dao;

interface UserApprovalDao
{
	function getApproval($id);

	function addApproval($approval);

	function updateApproval($id, $fields);

	function getLastestApprovalByUserIdAndStatus($userId, $status);

	function findApprovalsByUserIds($userIds);
}