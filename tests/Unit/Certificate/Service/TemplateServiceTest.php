<?php

namespace Tests\Unit\Certificate\Service;

use Biz\BaseTestCase;
use Biz\Certificate\Dao\TemplateDao;
use Biz\Certificate\Service\TemplateService;

class TemplateServiceTest extends BaseTestCase
{
    public function testGet()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->get($template['id']);

        $this->assertEquals('test', $res['name']);
    }

    public function testCreate()
    {
        $res = $this->getTemplateService()->create(['name' => 'name', 'targetType' => 'course']);

        $this->assertEquals('name', $res['name']);
    }

    public function testUpdate()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->update($template['id'], ['name' => 'testname']);

        $this->assertEquals('testname', $res['name']);
    }

    public function testUpdateBaseMap()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->updateBaseMap($template['id'], 'testUri');

        $this->assertEquals('testUri', $res['basemap']);
    }

    public function testUpdateStamp()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->updateStamp($template['id'], 'testUri');

        $this->assertEquals('testUri', $res['stamp']);
    }

    public function testCount()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->count(['nameLike' => 'es']);

        $this->assertEquals(1, $res);
    }

    public function testSearch()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->search(['nameLike' => 'es'], [], 0, 10);

        $this->assertEquals('test', $res[0]['name']);
    }

    public function testDropTemplate()
    {
        $template = $this->createTemplate();
        $res = $this->getTemplateService()->dropTemplate($template['id']);

        $this->assertEquals(1, $res['dropped']);
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
     * @return TemplateService
     */
    private function getTemplateService()
    {
        return $this->createService('Certificate:TemplateService');
    }
}
