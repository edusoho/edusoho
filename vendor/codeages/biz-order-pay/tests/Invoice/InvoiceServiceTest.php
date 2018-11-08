<?php

namespace Tests;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Pay\Service\Impl\PayServiceImpl;

class InvoiceServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $currentUser = array(
            'id' => 1
        );
        $this->biz['user'] = $currentUser;
    }

    public function testApplyInvoice()
    {
        $mockInvoice = $this->mockInvoice();

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
        $this->assertEquals($mockInvoice['title'], $invoice['title']);
        $this->assertEquals('unchecked', $invoice['status']);

        $default = $this->getInvoiceTemplateService()->getDefaultTemplate($this->biz['user']['id']);

        $this->assertNull($default);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testApplyInvoiceWithWrongMoney()
    {
        $mockInvoice = $this->mockInvoice();
        $mockInvoice['money'] = 2;

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testApplyInvoiceWithWrongUser()
    {
        $mockInvoice = $this->mockInvoice();
        $this->biz['user'] = array(
            'id' => 7
        );

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testApplyInvoiceWithOrderInvoiced()
    {
        $mockInvoice = $this->mockInvoice();

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
    }

    public function testGetInvoice()
    {
        $mockInvoice = $this->mockInvoice();
        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);

        $result = $this->getInvoiceService()->getInvoice($invoice['id']);

        $this->assertEquals($mockInvoice['title'], $result['title']);
        $this->assertEquals('unchecked', $result['status']);
    }

    public function testFinishInvoice()
    {
        $mockInvoice = $this->mockInvoice();
        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);

        $result = $this->getInvoiceService()->finishInvoice($invoice['id'], array(
            'status' => 'sent',
            'post_number' => 'foo',
            'refuse_comment' => 'bar'
        ));

        $this->assertEquals($mockInvoice['title'], $result['title']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals('foo', $result['post_number']);
        $this->assertEquals('bar', $result['refuse_comment']);
    }

    public function testCountInvoices()
    {
        $mockInvoice = $this->mockInvoice();
        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);

        $count = $this->getInvoiceService()->countInvoices([]);

        $this->assertEquals(1, $count);
    }

    public function testSearchInvoices()
    {
        $mockInvoice = $this->mockInvoice();

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);

        $invoices = $this->getInvoiceService()->searchInvoices([], [], 0, PHP_INT_MAX);

        $this->assertEquals(1, count($invoices));
        $this->assertEquals($mockInvoice['title'], $invoices[0]['title']);
    }

    protected function mockInvoice()
    {
        $trades = $this->createTrade();

        return array(
            'title' => 'foo',
            'type' => 'vat',
            'taxpayer_identity' => '131313131313',
            'content' => '培训费',
            'comment' => 'comment eg',
            'email' => 'tinyyywood@xxx.com',
            'address' => 'hangzhou zhejiang',
            'phone' => '15700081111',
            'receiver' => 'tinyyywood',
            'ids' => $trades['id'],
            'money' => 1,
        );
    }

    protected function createTrade()
    {
        $data = array(
            'title' => '人工智能神经网络1',
            'platform' => 'alipay',
            'trade_sn' => '2018101015062919708',
            'order_sn' => '2018101015054845150',
            'status' => 'paid',
            'cash_amount' => 100,
            'amount' => 100,
            'type' => 'purchase',
            'user_id' => 1,
            'platform_sn' => '2018101022001471650587146649',
            'price_type' => 'money',
        );

        return $this->getPayTradeDao()->create($data);
    }

    protected function getInvoiceService()
    {
        return $this->biz->service('Invoice:InvoiceService');
    }

    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    protected function getInvoiceTemplateService()
    {
        return $this->biz->service('Invoice:InvoiceTemplateService');
    }

    /**
     * @return \Codeages\Biz\Pay\Service\Impl\PayServiceImpl
     */
    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }

    protected function getPayTradeDao()
    {
        return $this->biz->dao('Pay:PayTradeDao');
    }
}
