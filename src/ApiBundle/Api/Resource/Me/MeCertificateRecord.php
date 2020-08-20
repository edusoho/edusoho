<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;

class MeCertificateRecord extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\CertificateRecord\CertificateRecordGroupFilter", mode="public")
     */
    public function search(ApiRequest $request)
    {
        $conditions = [
            'userId' => $this->getCurrentUser()['id'],
            'statuses' => ['valid', 'expired'],
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $total = $this->getCertificateRecordService()->count($conditions);

        $certificateRecords = $this->getCertificateRecordService()->search(
            $conditions,
            ['issueTime' => 'DESC', 'createdTime' => 'DESC'],
            $offset,
            $limit
        );

        return $this->makePagingObject($this->wrapperCertificateRecords($certificateRecords), $total, $offset, $limit);
    }

    protected function wrapperCertificateRecords(array $certificateRecords)
    {
        $certificates = $this->getCertificateService()->findByIds(ArrayToolkit::column($certificateRecords, 'certificateId'));
        $wrapperCertificateRecords = [];
        foreach ($certificateRecords as $certificateRecord) {
            $certificateRecord['certificate'] = empty($certificates[$certificateRecord['certificateId']]) ? '' : $certificates[$certificateRecord['certificateId']];
            $issueYear = date('Y', $certificateRecord['issueTime']);
            if (!isset($wrapperCertificateRecords[$issueYear])) {
                $wrapperCertificateRecords[$issueYear] = ['issueYear' => $issueYear, 'certificateRecords' => []];
            }
            $wrapperCertificateRecords[$issueYear]['certificateRecords'][] = $certificateRecord;
        }

        return array_values($wrapperCertificateRecords);
    }

    /**
     * @return CertificateService
     */
    public function getCertificateService()
    {
        return $this->getBiz()->service('Certificate:CertificateService');
    }

    /**
     * @return RecordService
     */
    public function getCertificateRecordService()
    {
        return $this->getBiz()->service('Certificate:RecordService');
    }
}
