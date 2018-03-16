<?php

namespace Tests;

class InvoiceTemplateServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $currentUser = array(
            'id' => 1
        );
        $this->biz['user'] = $currentUser;
    }

    public function testCreateInvoiceTemplate()
    {
        $mockTemplate = $this->mockMyTemplate();

        $template = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $this->assertEquals($mockTemplate['title'], $template['title']);
        $this->assertEquals(1, $template['is_default']);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateInvoiceTemplateWithoutTitle()
    {
        $mockTemplate = $this->mockMyTemplate();
        unset($mockTemplate['title']);

        $template = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);
    }

    public function testUpdateInvoiceTemplate()
    {
        $mockTemplate = $this->mockMyTemplate();
        $template = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $newtemplate = $this->getInvoiceTemplateService()->updateInvoiceTemplate($template['id'], array(
            'title' => 'bar',
            'type' => 'company',
            'taxpayer_identity' => $template['taxpayer_identity'],
            'address' => $template['address'],
            'phone' => $template['phone'],
            'email' => $template['email'],
            'receiver' => $template['receiver'],
        ));

        $this->assertEquals('bar', $newtemplate['title']);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUpdateInvoiceTemplateWithInvalidArgumentException()
    {
        $mockTemplate = $this->mockMyTemplate();
        $template = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $newtemplate = $this->getInvoiceTemplateService()->updateInvoiceTemplate($template['id'], array(
            'title' => 'bar',
        ));
    }

    public function testGetInvoiceTemplate()
    {
        $mockTemplate = $this->mockMyTemplate();
        $template = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $result = $this->getInvoiceTemplateService()->getInvoiceTemplate($template['id']);

        $this->assertEquals($mockTemplate['title'], $result['title']);
        $this->assertEquals(1, $result['is_default']);
        $this->assertEquals($mockTemplate['address'], $result['address']);
    }

    public function testDeleteInvoiceTemplate()
    {
        $mockTemplate = $this->mockMyTemplate();
        $template = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $this->getInvoiceTemplateService()->deleteInvoiceTemplate($template['id']);

        $template = $this->getInvoiceTemplateService()->getInvoiceTemplate($template['id']);

        $this->assertNull($template);
    }

    public function testSearchInvoiceTemplates()
    {
        $mockTemplate = $this->mockMyTemplate();
        $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $templates = $this->getInvoiceTemplateService()->searchInvoiceTemplates([], [], 0, PHP_INT_MAX);

        $this->assertEquals(1, count($templates));
    }

    public function testCountInvoiceTemplates()
    {
        $mockTemplate = $this->mockMyTemplate();
        $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $count = $this->getInvoiceTemplateService()->countInvoiceTemplates([]);

        $this->assertEquals(1, $count);
    }

    public function testSetDefalutTemplate()
    {
        $mockTemplate = $this->mockMyTemplate();
        $template1 = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);
        $mockTemplate['title'] = 'bar';
        $template2 = $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $this->assertEquals(1, $template1['is_default']);
        $this->assertEquals(0, $template2['is_default']);

        $template2 = $this->getInvoiceTemplateService()->setDefaultTemplate($template2['id']);
        $template1 = $this->getInvoiceTemplateService()->getInvoiceTemplate($template1['id']);

        $this->assertEquals(1, $template2['is_default']);
        $this->assertEquals(0, $template1['is_default']);
    }

    public function testGetDefaultTemplate()
    {
        $mockTemplate = $this->mockMyTemplate();
        $this->getInvoiceTemplateService()->createInvoiceTemplate($mockTemplate);

        $template = $this->getInvoiceTemplateService()->getDefaultTemplate($mockTemplate['user_id']);

        $this->assertEquals(1, $template['is_default']);
        $this->assertEquals($mockTemplate['title'], $template['title']);
    }

    protected function mockMyTemplate()
    {
        return array(
            'title' => 'foo',
            'type' => 'company',
            'taxpayer_identity' => '131313131313',
            'content' => '培训费',
            'comment' => 'comment eg',
            'email' => 'tinyyywood@xxx.com',
            'address' => 'hangzhou zhejiang',
            'phone' => '15700081111',
            'receiver' => 'tinyyywood',
            'user_id' => $this->biz['user']['id']
        );
    }

    protected function getInvoiceTemplateService()
    {
        return $this->biz->service('Invoice:InvoiceTemplateService');
    }
}