<?php

namespace ApiBundle\Api\Resource\CertificateRecord;

use ApiBundle\Api\Resource\Certificate\CertificateFilter;
use ApiBundle\Api\Resource\Filter;

class CertificateRecordFilter extends Filter
{
    protected $publicFields = [
        'id',
        'userId',
        'truename',
        'certificateId',
        'certificateCode',
        'targetType',
        'targetId',
        'status',
        'rejectReason',
        'auditUserId',
        'auditTime',
        'expiryTime',
        'issueTime',
        'createdTime',
        'updatedTime',
        'certificate',
        'imgUrl',
        'qrcodeUrl',
    ];

    protected function publicFields(&$data)
    {
        '0' !== $data['auditTime'] && $data['auditTime'] = date('c', $data['auditTime']);
        '0' !== $data['expiryTime'] && $data['expiryTime'] = date('c', $data['expiryTime']);
        '0' !== $data['issueTime'] && $data['issueTime'] = date('c', $data['issueTime']);

        if (!empty($data['certificate'])) {
            $certificateFilter = new CertificateFilter();
            $certificateFilter->setMode(Filter::SIMPLE_MODE);
            $certificateFilter->filter($data['certificate']);
        }
    }
}
