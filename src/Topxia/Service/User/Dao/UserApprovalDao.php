<?php
namespace Topxia\Service\User\Dao;

interface UserApprovalDao
{
	function getApproval($id);

	function addApproval($approval);

	function updateApproval($id, $fields);

	function getLastestApprovalByUserIdAndStatus($userId, $status);

	function findApprovalsByUserIds($userIds);

	function searchapprovals($conditions, $orderBy, $start, $limit);

	function searchapprovalsCount($conditions);
}