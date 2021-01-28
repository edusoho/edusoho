<?php

namespace AppBundle\Component\Export\Invite;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class InviteRecordsExporter extends Exporter
{
    public function getTitles()
    {
        return array(
            'admin.operation_invite.invite_code_owner',
            'admin.operation_invite.register_user',
            'admin.operation_invite.payingUserTotalPrice_th',
            'admin.operation_invite.coinAmountPrice_th',
            'admin.operation_invite.amountPrice_th',
            'user.register.invite_code_label',
            'user.account.my_invite_code.invite_time',
        );
    }

    public function canExport()
    {
        $user = $this->getUser();

        if ($user->hasPermission('admin_operation_invite_record') || $user->hasPermission('admin_v2_operation_invite_record')) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        $inviteUserCount = $this->getInviteRecordService()->countRecords($this->conditions);
        $invitedUserCount = 0;

        if (!empty($this->conditions['inviteUserId'])) {
            $conditions = $this->conditions;
            $conditions['invitedUserId'] = $conditions['inviteUserId'];
            unset($conditions['inviteUserId']);
            $invitedUserCount = $this->getInviteRecordService()->countRecords($conditions);
        }

        return !empty($inviteUserCount) ? $inviteUserCount : $invitedUserCount;
    }

    public function getContent($start, $limit)
    {
        $conditions = $this->conditions;

        $recordData = array();
        $records = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array('inviteTime' => 'desc'),
            $start,
            $limit
        );

        if (0 == $start) {
            if (!empty($this->conditions['inviteUserId'])) {
                $invitedRecord = $this->getInviteRecordService()->getRecordByInvitedUserId($this->conditions['inviteUserId']);
                if (!empty($invitedRecord)) {
                    array_unshift($records, $invitedRecord);
                }
            }
        }

        $users = $this->getInviteRecordService()->getAllUsersByRecords($records);

        foreach ($records as $record) {
            $content = $this->exportDataByRecord($record, $users);
            $recordData[] = $content;
        }

        return $recordData;
    }

    protected function exportDataByRecord($record, $users)
    {
        $content = array();
        $content[] = $users[$record['inviteUserId']]['nickname'];
        $content[] = $users[$record['invitedUserId']]['nickname'];
        $content[] = $record['amount'];
        $content[] = $record['coinAmount'];
        $content[] = $record['cashAmount'];
        $content[] = $users[$record['inviteUserId']]['inviteCode'];
        $content[] = date('Y-m-d H:i:s', $record['inviteTime']);

        return $content;
    }

    public function buildCondition($conditions)
    {
        $conditions = ArrayToolkit::parts($conditions, array('nickname', 'startDate', 'endDate'));
        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];
            unset($conditions['nickname']);
        }

        return $conditions;
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
