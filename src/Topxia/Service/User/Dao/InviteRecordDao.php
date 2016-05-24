<?php
namespace Topxia\Service\User\Dao;

interface InviteRecordDao
{
    public function findRecordsByInviteUserId($userId);

    public function addInviteRecord($record);

    public function getRecordByInvitedUserId($invitedUserId);

    public function updateInviteRecord($invitedUserId, $fields);

    public function searchRecordCount($conditions);

    public function searchRecords($conditions, $orderBy, $start, $limit);
}
