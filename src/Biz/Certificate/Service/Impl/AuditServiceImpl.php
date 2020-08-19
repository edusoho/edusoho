<?php


namespace Biz\Certificate\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Dao\CertificateDao;
use Biz\Certificate\Service\AuditService;
use Biz\Certificate\Service\ExamineService;
use Biz\Certificate\Dao\RecordDao;


class AuditServiceImpl extends BaseService implements AuditService
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

    public function update($id, $fields)
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD());
        }

        $fields = $this->filterRecordFields($fields);

        return $this->getRecordDao()->update($id, $fields);
    }

    protected function filterRecordFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            [
                'status',
                'auditTime',
                'auditUserId'
            ]
        );
    }

    public function passCertificate($id, $fields)
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('valid' == $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_PASS_RECORD);
        }

        $fields = ArrayToolkit::parts($fields, ['auditTime', 'auditUserId']);

        $fields['status'] = 'valid';

        return $this->getRecordDao()->update($record, $fields);
    }

    public function rejectCertificate($id, $fields)
    {
        $record = $this->get($id);
        if (empty($record)) {
            $this->createNewException(CertificateException::NOTFOUND_RECORD);
        }

        if ('reject' != $record['status'] || '' != $record['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_REJECT_RECORD);
        }

        $fields = ArrayToolkit::parts($fields, ['auditTime', 'auditUserId']);

        $fields['status'] = 'reject';

        return $this->getRecordDao()->update($record, $fields);
    }

    /**
     * @return CertificateDao
     */
    protected function getCertificateDao()
    {
        return $this->createDao('Certificate:CertificateDao');
    }

    /**
     * @return RecordDao
     */
    protected function getRecordDao()
    {
        return $this->createDao('Certificate:RecordDao');
    }

}