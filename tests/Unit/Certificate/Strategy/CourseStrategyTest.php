<?php

namespace Tests\Unit\Certificate\Strategy;

use Biz\BaseTestCase;
use Biz\Certificate\Dao\CertificateDao;
use Biz\Certificate\Dao\TemplateDao;
use Biz\Certificate\Strategy\CertificateStrategyContext;

class CourseStrategyTest extends BaseTestCase
{
    public function testGetTargetModal()
    {
        $strategy = $this->getCourseStrategy();
        $res = $strategy->getTargetModal();

        $this->assertEquals('admin-v2/operating/certificate/target/course-modal.html.twig', $res);
    }

    public function testCount()
    {
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'countCourseSets',
                'withParams' => [['status' => 'published', 'parentId' => 0, 'types' => ['normal', 'live']]],
                'returnValue' => 1,
            ],
        ]);
        $strategy = $this->getCourseStrategy();
        $res = $strategy->count([]);

        $this->assertEquals(1, $res);
    }

    public function testSearch()
    {
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'searchCourseSets',
                'withParams' => [['status' => 'published', 'parentId' => 0, 'types' => ['normal', 'live']], [], 0, 10],
                'returnValue' => [['id' => 1]],
            ],
        ]);
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'findCoursesByCourseSetIds',
                'returnValue' => [['id' => 1, 'courseSetId' => 1, 'status' => 'published']],
            ],
        ]);

        $strategy = $this->getCourseStrategy();
        $res = $strategy->search([], [], 0, 10);

        $this->assertEquals(1, $res[0]['id']);
    }

    public function testGetTarget()
    {
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'getCourseSet',
                'returnValue' => ['id' => 1],
            ],
        ]);
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'courseSetId' => 1, 'status' => 'published'],
            ],
        ]);
        $strategy = $this->getCourseStrategy();
        $res = $strategy->getTarget(1);

        $this->assertEquals(1, $res['id']);
    }

    public function testFindTargetsByIds()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'findCoursesByIds',
                'returnValue' => [['id' => 1, 'courseSetTitle' => 'title', 'courseSetId' => 1, 'status' => 'published']],
            ],
        ]);
        $strategy = $this->getCourseStrategy();
        $res = $strategy->findTargetsByIds([1]);

        $this->assertEquals('title', $res[0]['title']);
    }

    public function testFindTargetsByTargetTitle()
    {
        $strategy = $this->getCourseStrategy();
        $res = $strategy->findTargetsByTargetTitle('');
        $this->assertEquals(0, count($res));
    }

    protected function getCourseStrategy()
    {
        $context = new CertificateStrategyContext($this->getBiz());

        return $context->createStrategy('course');
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
