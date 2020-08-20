<?php

namespace ApiBundle\Api\Resource\CertificateRecord;

use ApiBundle\Api\Resource\Filter;

class CertificateRecordGroupFilter extends Filter
{
    protected $publicFields = [
        'issueYear', 'certificateRecords',
    ];

    protected function publicFields(&$data)
    {
        foreach ($data['certificateRecords'] as &$certificateRecord) {
            $certificateRecordFilter = new CertificateRecordFilter();
            $certificateRecordFilter->filter($certificateRecord);
        }
    }
}
