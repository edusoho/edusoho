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

        if ('valid' != $record['status']) {
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

        $fields = ArrayToolkit::parts($fields, ['issueTime']);

        $fields['status'] = 'valid';

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

    public function autoIssueCertificates($certificateId, $userIds)
    {
        $certificate = $this->getCertificateService()->get($certificateId);
        if (empty($certificate) || empty($certificate['autoIssue']) || empty($userIds)) {
            return true;
        }

        $this->beginTransaction();
        try {
            $userIds = $this->filterHasCertificateUsers($certificate, $userIds);
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
            'status' => 'valid',
            'issueTime' => time(),
            'expiryTime' => empty($certificate['expiryDay']) ? 0 : strtotime(date('Y-m-d', time() + 24 * 3600 * (int) $certificate['expiryDay'])),
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
            $generateCode = $certificate['certificateCode'].mt_rand(100000, 999999);
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
            if (empty($existedRecords[$userId]) || 'reject' != $existedRecords[$userId]['status']) {
                $filterUserIds[] = $userId;
            }
        }

        return $filterUserIds;
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

    /**
     * @return RecordDao
     */
    protected function getRecordDao()
    {
        return $this->createDao('Certificate:RecordDao');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
