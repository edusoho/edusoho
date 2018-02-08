<?php

namespace Tests;

class InvoiceServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $currentUser = array(
            'id' => 1,
        );
        $this->biz['user'] = $currentUser;
    }

    public function testApplyInvoice()
    {
        $mockInvoice = $this->mockInvoice();

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
        $this->assertEquals($mockInvoice['title'], $invoice['title']);
        $this->assertEquals('unchecked', $invoice['status']);

        $default = $this->getInvoiceTemplateService()->getDefaultTemplate($invoice['user_id']);
        $this->assertNotNull($default);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testApplyInvoiceWithWrongMoney()
    {
        $mockInvoice = $this->mockInvoice();
        $mockInvoice['money'] = 1;

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testApplyInvoiceWithWrongUser()
    {
        $mockInvoice = $this->mockInvoice();
        $this->biz['user'] = array(
            'id' => 2,
        );

        $invoice = $this->getInvoiceService()->applyInvoice($mockInvoice);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
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
            'post_number' => 'foo',
            'review_comment' => 'bar',
        ));

        $this->assertEquals($mockInvoice['title'], $result['title']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals('foo', $result['post_number']);
        $this->assertEquals('bar', $result['review_comment']);
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
        $order = $this->createOrder();

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
            'orderIds' => $order['id'],
            'money' => $order['pay_amount'] / 100,
        );
    }

    protected function createOrder()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);

        return $order;
    }

    protected function mockOrderItems()
    {
        return array(
            array(
                'title' => '人工智能神经网络',
                'detail' => '<div>独创的教学</div>',
                'price_amount' => 100,
                'target_id' => 1,
                'target_type' => 'course',
                'create_extra' => array(
                    'xxx' => 'xxx',
                ),
                'deducts' => array(
                    array(
                        'deduct_id' => 1,
                        'deduct_type' => 'discount',
                        'deduct_amount' => 10,
                        'detail' => '打折活动扣除10元',
                    ),
                    array(
                        'deduct_id' => 2,
                        'deduct_type' => 'coupon',
                        'deduct_amount' => 8,
                        'detail' => '使用优惠码扣除8元',
                    ),
                ),
            ),
            array(
                'title' => 'F1驾驶技术',
                'detail' => '<div>F1任丘人发生的发个</div>',
                'price_amount' => 110,
                'target_id' => 2,
                'target_type' => 'course',
                'create_extra' => array(
                    'xxx' => 'xxx',
                ),
                'deducts' => array(
                    array(
                        'deduct_id' => 3,
                        'deduct_type' => 'discount',
                        'deduct_amount' => 10,
                        'detail' => '打折活动扣除10元',
                    ),
                    array(
                        'deduct_id' => 5,
                        'deduct_type' => 'coupon',
                        'deduct_amount' => 4,
                        'detail' => '使用优惠码扣除4元',
                    ),
                ),
            ),
        );
    }

    protected function mockOrder()
    {
        return array(
            'title' => '购买商品',
            'callback' => array('url' => 'http://try6.edusoho.cn/'),
            'source' => 'custom',
            'price_type' => 'coin',
            'user_id' => $this->biz['user']['id'],
            'created_reason' => '购买',
            'create_extra' => array(
                'xxx' => 'xxx',
            ),
            'device' => 'wap',
            'expired_refund_days' => 5,
        );
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
}
