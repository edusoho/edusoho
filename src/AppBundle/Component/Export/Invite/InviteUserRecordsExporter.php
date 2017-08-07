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

        if ($user->hasPermission('admin_operation_invite_user')) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $count = $this->getUserService()->countUsers($this->conditions);
    }

    public function getContent($start, $limit)
    {
        $conditions = $this->conditions;
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'ASC'),
            $start,
            $limit
        );

        $userRecordData = $this->getInviteRecordService()->getInviteInformationsByUsers($users);
        $userRecordData = $this->getUserRecordContent($userRecordData);

        return $userRecordData;
    }

    protected function getUserRecordContent($records)
    {
        $data = array();
        foreach ($records as $userRecordData) {
            $content = array();
            $content[] = $userRecordData['nickname'];
            $content[] = $userRecordData['count'];
            $content[] = $userRecordData['payingUserCount'];
            $content[] = $userRecordData['payingUserTotalPrice'];
            $content[] = $userRecordData['coinAmountPrice'];
            $content[] = $userRecordData['amountPrice'];
            $data[] = $content;
        }

        return $data;
    }

    public function buildCondition($conditions)
    {
        return ArrayToolkit::parts($conditions, array('nickname'));
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
