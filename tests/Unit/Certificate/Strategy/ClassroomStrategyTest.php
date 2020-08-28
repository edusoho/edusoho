<?php

namespace Tests\Unit\Certificate\Strategy;

use Biz\BaseTestCase;
use Biz\Certificate\Dao\CertificateDao;
use Biz\Certificate\Dao\TemplateDao;
use Biz\Certificate\Strategy\Impl\ClassroomStrategy;

class ClassroomStrategyTest extends BaseTestCase
{
    public function testGetTargetModal()
    {
        $strategy = $this->getClassroomStrategy();
        $res = $strategy->getTargetModal();

        $this->assertEquals('admin-v2/operating/certificate/target/classroom-modal.html.twig', $res);
    }

    public function testCount()
    {
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'countClassrooms',
                'withParams' => [['status' => 'published']],
                'returnValue' => 1,
            ],
        ]);
        $strategy = $this->getClassroomStrategy();
        $res = $strategy->count([]);

        $this->assertEquals(1, $res);
    }

    public function testSearch()
    {
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'searchClassrooms',
                'withParams' => [['status' => 'published'], [], 0, 10],
                'returnValue' => [['id' => 1]],
            ],
        ]);

        $strategy = $this->getClassroomStrategy();
        $res = $strategy->search([], [], 0, 10);

        $this->assertEquals(1, $res[0]['id']);
    }

    public function testGetTarget()
    {
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'returnValue' => ['id' => 1],
            ],
        ]);
        $strategy = $this->getClassroomStrategy();
        $res = $strategy->getTarget(1);

        $this->assertEquals(1, $res['id']);
    }

    public function testFindTargetsByIds()
    {
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'findClassroomsByIds',
                'returnValue' => [['id' => 1, 'status' => 'published']],
            ],
        ]);
        $strategy = $this->getClassroomStrategy();
        $res = $strategy->findTargetsByIds([1]);

        $this->assertEquals('published', $res[0]['status']);
    }

    public function testFindTargetsByTargetTitle()
    {
        $strategy = $this->getClassroomStrategy();
        $res = $strategy->findTargetsByTargetTitle('');
        $this->assertEquals(0, count($res));
    }

    protected function getClassroomStrategy()
    {
        return new ClassroomStrategy($this->getBiz());
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

    protected function createTemplate($template = [])
    {
        $default = [
            'name' => 'test',
            'targetType' => 'course',
            'certificateName' => 'cname',
            'recipientContent' => '$name$（$username$）同学：',
            'certificateContent' => '由于你在$courseName$ 课程中优异学习表现，最终完成课程并通过最终考核，特此发次证明！',
        ];
        $template = array_merge($default, $template);

        return $this->getTemplateDao()->create($template);
    }

    /**
     * @return TemplateDao
     */
    private function getTemplateDao()
    {
        return $this->createDao('Certificate:TemplateDao');
    }

    /**
     * @return CertificateDao
     */
    private function getCertificateDao()
    {
        return $this->createDao('Certificate:CertificateDao');
    }
}
