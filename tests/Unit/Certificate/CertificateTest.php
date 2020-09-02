<?php

namespace Tests\Unit\Certificate;

use Biz\BaseTestCase;
use Biz\Certificate\Certificate;

class CertificateTest extends BaseTestCase
{
    public function testSetCertificateParams()
    {
        $certificate = new Certificate();
        $certificate->setCertificateParams([
            'certificateTitle' => 'test',
            'certificateQrCodeUrl' => 'url',
            'certificateRecipient' => 'Recipient',
            'certificateContent' => 'test',
            'certificateCode' => 'code',
            'certificateExpiryTime' => 0,
            'certificateIssueTime' => 0,
            'certificateStamp' => 'stamp',
            'certificateBasemap' => 'basemap',
        ]);

        $this->assertEquals('test', $certificate->getCertificateTitle());
        $this->assertEquals('url', $certificate->getCertificateQrCodeUrl());
        $this->assertEquals('Recipient', $certificate->getCertificateRecipient());
        $this->assertEquals('test', $certificate->getCertificateTitle());
        $this->assertEquals('code', $certificate->getCertificateCode());
        $this->assertEquals(0, $certificate->getCertificateDeadline());
        $this->assertEquals(0, $certificate->getCertificateIssueTime());
        $this->assertEquals('stamp', $certificate->getCertificateStamp());
        $this->assertEquals('basemap', $certificate->getCertificateBasemap());
    }
}
