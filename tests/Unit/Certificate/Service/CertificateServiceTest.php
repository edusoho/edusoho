<?php

namespace Tests\Unit\Certificate\Service;

use Biz\BaseTestCase;
use Biz\Certificate\Dao\CertificateDao;
use Biz\Certificate\Service\CertificateService;

class CertificateServiceTest extends BaseTestCase
{
    public function testGet()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->get($certificate['id']);

        $this->assertEquals('test', $res['name']);
    }

    public function testGetCertificateByCode()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->getCertificateByCode('code');

        $this->assertEquals('test', $res['name']);
    }

    public function testFindByTargetIdAndTargetType()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->findByTargetIdAndTargetType(1, 'course');

        $this->assertEquals('test', $res[0]['name']);
    }

    public function testSearch()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->search(['nameLike' => 'es'], [], 0, 10);

        $this->assertEquals('test', $res[0]['name']);

        $res = $this->getCertificateService()->search(['nameLike' => 'tt'], [], 0, 10);

        $this->assertEmpty($res);
    }

    public function testFindByIds()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->findByIds([$certificate['id']]);

        $this->assertEquals('test', current($res)['name']);
    }

    public function testCount()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->count(['nameLike' => 'es']);

        $this->assertEquals(1, $res);

        $res = $this->getCertificateService()->count(['nameLike' => 'tt']);

        $this->assertEquals(0, $res);
    }

    public function testCreate()
    {
        $res = $this->getCertificateService()->create(['name' => 'name', 'code' => 'code']);

        $this->assertEquals('name', $res['name']);
    }

    public function testUpdate()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->update($certificate['id'], ['name' => 'name']);

        $this->assertEquals('name', $res['name']);
    }

    public function testPublishCertificate()
    {
        $certificate = $this->createCertificate();
        $res = $this->getCertificateService()->publishCertificate($certificate['id']);

        $this->assertEquals('published', $res['status']);
    }

    public function testCloseCertificate()
    {
        $certificate = $this->createCertificate();
        $certificate = $this->getCertificateService()->publishCertificate($certificate['id']);

        $res = $this->getCertificateService()->closeCertificate($certificate['id']);

        $this->assertEquals('closed', $res['status']);
    }

    public function testDelete()
    {
        $certificate = $this->createCertificate();
        $this->getCertificateService()->delete($certificate['id']);
        $res = $this->getCertificateService()->get($certificate['id']);

        $this->assertEmpty($res);
    }

    public function testSearchUserAvailableCertificates()
    {
        $certificate = $this->createCertificate(['status' => 'published']);
        $this->mockBiz('Certificate:RecordService', [
            [
                'functionName' => 'search',
                'returnValue' => [
                    ['certificateId' => $certificate['id']],
                ],
            ],
        ]);

        $res = $this->getCertificateService()->searchUserAvailableCertificates(1, '', 0, 10);
        $this->assertEmpty($res);
    }

    public function testCountUserAvailableCertificates()
    {
        $certificate = $this->createCertificate(['status' => 'published']);
        $this->mockBiz('Certificate:RecordService', [
            [
                'functionName' => 'search',
                'returnValue' => [
                    ['certificateId' => $certificate['id']],
                ],
            ],
        ]);

        $res = $this->getCertificateService()->countUserAvailableCertificates(1, '');
        $this->assertEquals(0, $res);
    }

    protected function createCertificate($certificate = [])
    {
        $default = [
            'name' => 'test',
            'targetType' => 'course',
            'targetId' => 1,
            'templateId' => 1,
            'code' => 'code',
        ];
        $certificate = array_merge($default, $certificate);

        return $this->getCertificateDao()->create($certificate);
    }

    /**
     * @return CertificateDao
     */
    private function getCertificateDao()
    {
        return $this->createDao('Certificate:CertificateDao');
    }

    /**
     * @return CertificateService
     */
    private function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }
}
