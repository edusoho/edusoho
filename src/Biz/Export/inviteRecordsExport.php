<?php

namespace Biz\Export;

use AppBundle\Common\ExportHelp;
use AppBundle\Common\ArrayToolkit;

class inviteRecordsExport extends Exporter
{
    public function getTitles()
    {
        return array('邀请人', '注册用户', '订单消费总额', '订单虚拟币总额', '订单现金总额', '邀请码', '邀请时间');
    }

    public function canExport()
    {
        $biz = $this->biz;
        $user = $biz['user'];
        if ($user->hasPermission('admin_operation_invite_record')) {
            return true;
        }

        return false;
    }

    public function getExportContent($start, $limit)
    {
        $conditions = $this->conditions;
        $conditions = ArrayToolkit::parts($conditions, array('nickname', 'startDate', 'endDate'));

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];
            unset($conditions['nickname']);
        }

        $recordCount = $this->getInviteRecordService()->countRecords($conditions);

        $recordData = array();
        $records = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array(),
            $start,
            $limit
        );

        if ($start == 0) {
            if (!empty($user)) {
                $invitedRecord = $this->getInviteRecordService()->getRecordByInvitedUserId($user['id']);
                array_unshift($records, $invitedRecord);
            }
        }

        $users = $this->getInviteRecordService()->getAllUsersByRecords($records);

        foreach ($records as $record) {
            $content = $this->exportDataByRecord($record, $users);
            $recordData[] = $content;
        }

        return array($recordData, $recordCount);
    }

    protected function exportDataByRecord($record, $users)
    {
        list($coinAmountTotalPrice, $amountTotalPrice, $totalPrice) = $this->getInviteRecordService()->getUserOrderDataByUserIdAndTime($record['invitedUserId'], $record['inviteTime']);
        $content = '';
        $content .= $users[$record['inviteUserId']]['nickname'].',';
        $content .= $users[$record['invitedUserId']]['nickname'].',';
        $content .= $totalPrice.',';
        $content .= $coinAmountTotalPrice.',';
        $content .= $amountTotalPrice.',';
        $content .= $users[$record['inviteUserId']]['inviteCode'].',';
        $content .= date('Y-m-d H:i:s', $record['inviteTime']).',';

        return $content;
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getInviteRecordService()
    {
        return $this->biz->service('User:InviteRecordService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}