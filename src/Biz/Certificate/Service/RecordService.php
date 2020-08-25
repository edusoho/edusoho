<?php

namespace Biz\Certificate\Service;

interface RecordService
{
    public function get($id);

    public function count($conditions);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function findExpiredRecords($certificateId);

    public function findRecordsByCertificateId($certificateId);

    public function cancelRecord($id);

    public function grantRecord($id, $fields);

    public function autoIssueCertificates($certificateId, $userIds);

    public function isObtained($userId, $certificateId);

    public function isCertificatesObtained($userId, $certificateIds);

    public function passCertificateRecord($id, $auditUserId, $rejectReason = '');

    public function rejectCertificateRecord($id, $auditUserId, $rejectReason = '');

    public function resetCertificateRecord($id, $rejectReason = '');

    public function checkExpireCertificate();
}
