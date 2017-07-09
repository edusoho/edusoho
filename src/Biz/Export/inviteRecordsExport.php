<?php

namespace Biz\Export;

use AppBundle\Common\ExportHelp;
use AppBundle\Common\ArrayToolkit;

class inviteRecordsExport extends Exporter
{
    public function getValue()
    {

    }

    public function getTitles()
    {
        $title = array('邀请人', '注册用户', '订单消费总额', '订单虚拟币总额', '订单现金总额', '邀请码', '邀请时间');

    }

    public function getExportContent()
    {

    }


    public function getPreResult()
    {
        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($this->request);

        $conditions = $this->request->query->all();
        $conditions = ArrayToolkit::parts($conditions, array('nickname', 'startDate', 'endDate'));
        $nickname = $this->request->query->get('nickname');
        if (!empty($nickname)) {
            $user = $this->getUserService()->getUserByNickname($nickname);
            $conditions['inviteUserId'] = empty($user) ? '0' : $user['id'];
            unset($conditions['nickname']);
        }

        list($records, $recordCount) = $this->getExportRecordContent(
            $start,
            $limit,
            $conditions,
            $exportAllowCount
        );

        $title = '邀请人,注册用户,订单消费总额,订单虚拟币总额,订单现金总额,邀请码,邀请时间';
        $file = '';
        if ($start == 0) {
            $file = ExportHelp::addFileTitle($this->request, 'invite_record', $title);

            if (!empty($user)) {
                $invitedRecord = $this->getInvitedRecordByUserIdAndConditions($user, $conditions);
                if ($invitedRecord) {
                    $users = $this->getAllUsersByRecords($invitedRecord);
                    $invitedExportContent = $this->exportDataByRecord(reset($invitedRecord), $users);
                    $file = ExportHelp::saveToTempFile($this->request, $invitedExportContent, $file);
                }
            }
        }

        $content = implode("\r\n", $records);
        $file = ExportHelp::saveToTempFile($this->request, $content, $file);

        $status = ExportHelp::getNextMethod($start + $limit, $recordCount);

        return array(
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
        );
    }

    public function getExportRecordContent($start, $limit, $conditions, $exportAllowCount)
    {
        $recordCount = $this->getInviteRecordService()->countRecords($conditions);

        $recordCount = ($recordCount > $exportAllowCount) ? $exportAllowCount : $recordCount;
        if ($recordCount < ($start + $limit + 1)) {
            $limit = $recordCount - $start;
        }

        $recordData = array();
        $records = $this->getInviteRecordService()->searchRecords(
            $conditions,
            array(),
            $start,
            $limit
        );
        $users = $this->getAllUsersByRecords($records);

        foreach ($records as $record) {
            $content = $this->exportDataByRecord($record, $users);
            $recordData[] = $content;
        }

        return array($recordData, $recordCount);
    }

    protected function getAllUsersByRecords($records)
    {
        $inviteUserIds = ArrayToolkit::column($records, 'inviteUserId');
        $invitedUserIds = ArrayToolkit::column($records, 'invitedUserId');
        $userIds = array_merge($inviteUserIds, $invitedUserIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $users;
    }


    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getInviteRecordService()
    {
        return $this->biz->service('User:InviteRecordService');
    }
}