<?php

namespace AppBundle\Component\Export\Certificate;

use AppBundle\Component\Export\Exporter;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;

class CertificateRecordExporter extends Exporter
{
    public function getTitles()
    {
        return [
            'user.fields.truename_label',
            'user.fields.username_label',
            'admin.operation_certificate.certificate.batch',
            'admin.operation_certificate.certificate.record.code',
            'admin.operation_certificate.certificate.expiry_day',
            'admin.operation_certificate.certificate.record.status',
            'admin.operation_certificate.certificate.record.issue_date',
        ];
    }

    public function getContent($start, $limit)
    {
        $certificate = $this->getCertificateService()->get($this->conditions['certificateId']);
        $records = $this->getRecordService()->search(
            $this->conditions,
            ['createdTime' => 'desc'],
            $start,
            $limit
        );
        $users = $this->getUserService()->findUsersByIds(array_column($records, 'userId'));
        $userProfiles = $this->getUserService()->findUserProfilesByIds(array_column($records, 'userId'));
        $targets = $this->getCertificateStrategy($certificate['targetType'])->findTargetsByIds(array_column($records, 'targetId'));
        $dataSet = [];
        foreach ($records as $record) {
            $data = [];
            $data[] = empty($userProfiles[$record['userId']]['truename']) ? '--' : $userProfiles[$record['userId']]['truename'];
            $data[] = $users[$record['userId']]['nickname'] ?? '--';
            $data[] = $targets[$record['targetId']]['title'] ?? '--';
            $data[] = $record['certificateCode'];
            $data[] = empty($record['expiryTime']) ? '长期有效' : date('Y-m-d', $record['expiryTime']);
            $data[] = $this->transStatus($record);
            $data[] = date('Y-m-d', $record['issueTime']);
            $dataSet[] = $data;
        }

        return $dataSet;
    }

    public function canExport()
    {
        return $this->getUser()->hasPermission('admin_v2_certificate_manage');
    }

    public function getCount()
    {
        return $this->getRecordService()->count($this->conditions);
    }

    public function buildCondition($conditions)
    {
        if (!empty($conditions['status']) && 'all' == $conditions['status']) {
            unset($conditions['status']);
        }
        if (!empty($conditions['keywordType']) && !empty($conditions['keyword'])) {
            if ('code' == $conditions['keywordType']) {
                $conditions['certificateCode'] = $conditions['keyword'];
            }
            if (in_array($conditions['keywordType'], ['nickname', 'verifiedMobile', 'email'])) {
                $users = $this->getUserService()->searchUsers([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
                $conditions['userIds'] = $users ? array_column($users, 'id') : [-1];
            }
            if ('truename' == $conditions['keywordType']) {
                $users = $this->getUserService()->searchUserProfiles([$conditions['keywordType'] => $conditions['keyword']], [], 0, PHP_INT_MAX, ['id']);
                $conditions['userIds'] = $users ? array_column($users, 'id') : [-1];
            }
            if ('batch' == $conditions['keywordType']) {
                $certificate = $this->getCertificateService()->get($conditions['certificateId']);
                $resource = $this->getCertificateStrategy($certificate['targetType'])->findTargetsByTargetTitle($conditions['keyword']);
                $conditions['targetIds'] = $resource ? array_column($resource, 'id') : [-1];
            }
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        $conditions['statusNotIn'] = ['none', 'reject'];

        return $conditions;
    }

    private function transStatus($record)
    {
        $status = $record['status'];
        if ('valid' === $status && $record['expiryTime'] > 0 && $record['expiryTime'] < time()) {
            $status = 'expired';
        }

        return $this->container->get('codeages_plugin.dict_twig_extension')->getDictText('certificateStatus', $status);
    }

    /**
     * @return RecordService
     */
    private function getRecordService()
    {
        return $this->getBiz()->service('Certificate:RecordService');
    }

    /**
     * @return CertificateService
     */
    private function getCertificateService()
    {
        return $this->getBiz()->service('Certificate:CertificateService');
    }

    private function getCertificateStrategy($type)
    {
        return $this->getBiz()->offsetGet('certificate.strategy_context')->createStrategy($type);
    }
}
