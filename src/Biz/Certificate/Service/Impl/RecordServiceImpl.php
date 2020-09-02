<?php

namespace Biz\Certificate\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Dao\RecordDao;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\System\Service\LogService;

class RecordServiceImpl extends BaseService implements RecordService
{
    public function get($id)
    {
        return $this->getRecordDao()->get($id);
    }

    public function count($conditions)
    {
        return $this->getRecordDao()->count($conditions);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getRecordDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function findExpiredRecords($certificateId)
    {
        return $this->getRecordDao()->findExpiredRecords($certificateId);
    }

    public function findRecordsByCertificateId($certificateId)
    {
        return $this->getRecordDao()->findByCertificateId($certificateId);
    }

    public function cancelRecord($id)
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD());
        }

        if ('cancelled' == $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_CANCEL_RECORD());
        }

        return $this->getRecordDao()->update($id, ['status' => 'cancelled']);
    }

    public function grantRecord($id, $fields)
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD());
        }

        if ('cancelled' != $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_CANCEL_RECORD());
        }

        $certificate = $this->getCertificateService()->get($record['certificateId']);
        $fields = ArrayToolkit::parts($fields, ['issueTime']);

        $fields['status'] = 'valid';
        $fields['expiryTime'] = empty($certificate['expiryDay']) ? 0 : strtotime(date('Y-m-d', $fields['issueTime'] + 24 * 3600 * (int) $certificate['expiryDay']));

        return $this->getRecordDao()->update($id, $fields);
    }

    public function isObtained($userId, $certificateId)
    {
        $isObtained = $this->getRecordDao()->search(
            ['userId' => $userId, 'certificateId' => $certificateId, 'statuses' => ['valid', 'expired']],
            [],
            0,
            1
        );

        return empty($isObtained) ? false : true;
    }

    public function isCertificatesObtained($userId, $certificateIds)
    {
        $obtaineds = $this->getRecordDao()->search(
            ['statuses' => ['valid', 'expired'], 'userId' => $userId, 'certificateIds' => $certificateIds],
            [],
            0,
            PHP_INT_MAX
        );

        $isObtaineds = [];
        $obtaineds = ArrayToolkit::index($obtaineds, 'certificateId');
        foreach ($certificateIds as $certificateId) {
            $isObtaineds[$certificateId] = isset($obtaineds[$certificateId]) ? true : false;
        }

        return $isObtaineds;
    }

    public function passCertificateRecord($id, $auditUserId, $rejectReason = '')
    {
        $record = $this->get($id);
        $certificate = $this->getCertificateService()->get($record['certificateId']);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('reject' !== $record['status'] && 'none' !== $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_VALID_RECORD);
        }

        $record['rejectReason'] = $rejectReason;
        $record['auditUserId'] = $auditUserId;
        $record['auditTime'] = $record['issueTime'] = time();
        $record['status'] = 'valid';
        $record['expiryTime'] = (0 == $certificate['expiryDay']) ? 0 : strtotime(date('Y-m-d', time() + 24 * 3600 * (int) $certificate['expiryDay']));

        return $this->getRecordDao()->update($id, $record);
    }

    public function rejectCertificateRecord($id, $auditUserId, $rejectReason = '')
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('reject' !== $record['status'] && 'none' !== $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_REJECT_RECORD);
        }

        $record['rejectReason'] = $rejectReason;
        $record['auditUserId'] = $auditUserId;
        $record['auditTime'] = $record['issueTime'] = time();
        $record['status'] = 'reject';

        return $this->getRecordDao()->update($id, $record);
    }

    public function resetCertificateRecord($id, $rejectReason = '')
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('reject' !== $record['status'] && 'none' !== $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_AUDIT_RECORD);
        }

        $record['rejectReason'] = $rejectReason;
        $record['auditUserId'] = null;
        $record['auditTime'] = null;
        $record['status'] = 'none';

        return $this->getRecordDao()->update($id, $record);
    }

    public function autoIssueCertificates($certificateId, $userIds)
    {
        $certificate = $this->getCertificateService()->get($certificateId);
        if (empty($certificate) || 'published' != $certificate['status'] || empty($userIds)) {
            return true;
        }

        $this->beginTransaction();
        try {
            $userIds = $this->filterHasCertificateUsers($certificate, $userIds);
            if (empty($userIds)) {
                $this->commit();

                return true;
            }
            $this->batchCreateCertificateRecords($certificate, $userIds);

            $this->getLogService()->info('certificate', 'auto_issue', '自动发放证书：'.json_encode($certificate).'用户ID：'.json_encode($userIds));

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogService()->error('certificate', 'auto_issue', '自动发放证书失败：'.json_encode($certificate).'用户ID：'.json_encode($userIds));
            throw $e;
        }

        return true;
    }

    protected function batchCreateCertificateRecords($certificate, $userIds)
    {
        $defaultRecord = [
            'certificateId' => $certificate['id'],
            'targetId' => $certificate['targetId'],
            'targetType' => $certificate['targetType'],
            'status' => empty($certificate['autoIssue']) ? 'none' : 'valid',
            'issueTime' => empty($certificate['autoIssue']) ? 0 : time(),
            'expiryTime' => empty($certificate['expiryDay']) ? 0 : strtotime(date('Y-m-d', time() + 24 * 3600 * (int) $certificate['expiryDay'])),
            'auditTime' => empty($certificate['autoIssue']) ? 0 : time(),
        ];
        $createRecords = [];
        $certificateCodes = $this->generateCertificateCode($certificate, count($userIds));
        foreach ($userIds as $key => $userId) {
            $defaultRecord['userId'] = $userId;
            $defaultRecord['certificateCode'] = $certificateCodes[$key];
            $createRecords[] = $defaultRecord;
        }

        if (!empty($createRecords)) {
            $this->getRecordDao()->batchCreate($createRecords);
        }

        return true;
    }

    protected function generateCertificateCode($certificate, $count)
    {
        $existCodes = $this->findRecordsByCertificateId($certificate['id']);
        $existCodes = ArrayToolkit::column($existCodes, 'certificateCode');
        $generateCodes = [];
        while (count($generateCodes) < $count) {
            $generateCode = $certificate['code'].mt_rand(100000, 999999);
            if (!in_array($generateCode, $existCodes) && !in_array($generateCode, $generateCodes)) {
                $generateCodes[] = $generateCode;
            }
        }

        return $generateCodes;
    }

    protected function filterHasCertificateUsers($certificate, $userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        $filterUserIds = [];
        $existedRecords = $this->getRecordDao()->findByUserIdsAndCertificateId($userIds, $certificate['id']);
        $existedRecords = ArrayToolkit::index($existedRecords, 'userId');
        foreach ($userIds as $userId) {
            if (empty($existedRecords[$userId]) || 'reject' == $existedRecords[$userId]['status']) {
                $filterUserIds[] = $userId;
            }
        }

        return $filterUserIds;
    }

    public function checkExpireCertificate()
    {
        $conditions = [
            'status' => 'valid',
            'expiryTime_LE' => strtotime(date('Y-m-d', strtotime('-1 day'))),
            'expiryTime_NE' => 0,
        ];
        $count = $this->getRecordDao()->count($conditions);
        $records = $this->getRecordDao()->search($conditions, [], 0, $count);
        if (empty($records)) {
            return;
        }

        $updateRecords = [];
        foreach ($records as $record) {
            $updateRecords[] = ['id' => $record['id'], 'status' => 'expired'];
        }

        return $this->getRecordDao()->batchUpdate(ArrayToolkit::column($updateRecords, 'id'), $updateRecords);
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }

    /**
     * @return RecordDao
     */
    protected function getRecordDao()
    {
        return $this->createDao('Certificate:RecordDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
