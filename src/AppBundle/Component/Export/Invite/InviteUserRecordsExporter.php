<?php

namespace AppBundle\Component\Export\Invite;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class InviteUserRecordsExporter extends Exporter
{
    public function getTitles()
    {
        return array(
            'admin.operation_invite.nickname_th',
            'admin.operation_invite.count_th',
            'admin.operation_invite.payingUserCount_th',
            'admin.operation_invite.payingUserTotalPrice_th',
            'admin.operation_invite.coinAmountPrice_th',
            'admin.operation_invite.amountPrice_th',
        );
    }

    public function canExport()
    {
        $user = $this->getUser();

        if ($user->hasPermission('admin_operation_invite_user') || $user->hasPermission('admin_v2_operation_invite_user')) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $this->getInviteRecordService()->countInviteUser($this->conditions);
    }

    public function getContent($start, $limit)
    {
        $records = $this->getInviteRecordService()->searchRecordGroupByInviteUserId(
            $this->conditions,
            $start,
            $limit
        );

        return $this->getUserRecordContent($records);
    }

    protected function getUserRecordContent($records)
    {
        $data = array();
        foreach ($records as $userRecordData) {
            $content = array();
            $content[] = $userRecordData['invitedUserNickname'];
            $content[] = $userRecordData['countInvitedUserId'];
            $content[] = $userRecordData['premiumUserCounts'];
            $content[] = $userRecordData['amount'];
            $content[] = $userRecordData['coinAmount'];
            $content[] = $userRecordData['cashAmount'];
            $data[] = $content;
        }

        return $data;
    }

    public function buildCondition($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['inviteUserId'] = $user['id'];
        }

        return ArrayToolkit::parts($conditions, array('inviteUserId'));
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getInviteRecordService()
    {
        return $this->getBiz()->service('User:InviteRecordService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
