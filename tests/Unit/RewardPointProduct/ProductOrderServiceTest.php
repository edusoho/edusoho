<?php

namespace Tests\Unit\RewardPointProduct;

use Biz\BaseTestCase;

class ProductOrderServiceTest extends BaseTestCase
{
    public function testCreateProductOrder()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => '笔记本',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'edusoho@howzhi.com',
            'address' => '越源大厦',
            'sendTime' => 1111111111,
            'status' => 'created',
        );

        $result = $this->getProductOrderService()->createProductOrder($fields);
        $this->assertEquals($fields['userId'], $result['userId']);
        $this->assertEquals($fields['productId'], $result['productId']);
    }

    public function testUpdateProductOrder()
    {
        $fields = array(
             'sn' => '1010',
             'productId' => 1,
             'title' => '笔记本',
             'price' => 454,
             'userId' => 1,
             'telephone' => '16732147311',
             'email' => 'edusoho@howzhi.com',
             'address' => '越源大厦',
             'sendTime' => 1111111111,
             'status' => 'created',
         );

        $createProductOrder = $this->getProductOrderService()->createProductOrder($fields);
        $updateRecord = $this->getProductOrderService()->updateProductOrder(
             $createProductOrder['id'],
             array('title' => '电脑')
         );

        $this->assertEquals('电脑', $updateRecord['title']);
        $this->assertEquals($createProductOrder['id'], $updateRecord['id']);
    }

    public function testGetProductOrder()
    {
        $fields = array(
             'sn' => '1010',
             'productId' => 1,
             'title' => '笔记本',
             'price' => 454,
             'userId' => 1,
             'telephone' => '16732147311',
             'email' => 'edusoho@howzhi.com',
             'address' => '越源大厦',
             'sendTime' => 1111111111,
             'status' => 'created',
         );

        $createProductOrder = $this->getProductOrderService()->createProductOrder($fields);
        $result = $this->getProductOrderService()->getProductOrder($createProductOrder['id']);
        $this->assertEquals($createProductOrder, $result);
    }

    public function testCountProductOrders()
    {
        $fields = array(
             'sn' => '1010',
             'productId' => 1,
             'title' => '笔记本',
             'price' => 454,
             'userId' => 1,
             'telephone' => '16732147311',
             'email' => 'edusoho@howzhi.com',
             'address' => '越源大厦',
             'sendTime' => 1111111111,
             'status' => 'created',
         );

        $this->getProductOrderService()->createProductOrder($fields);

        $count = $this->getProductOrderService()->countProductOrders(array());
        $this->assertEquals(1, $count);

        $count = $this->getProductOrderService()->countProductOrders(
             array(
                 'userId' => 2,
             )
         );

        $this->assertEquals(0, $count);
    }

    public function testSearchProductOrders()
    {
        $fields = array(
             'sn' => '1010',
             'productId' => 1,
             'title' => '笔记本',
             'price' => 454,
             'userId' => 1,
             'telephone' => '16732147311',
             'email' => 'edusoho@howzhi.com',
             'address' => '越源大厦',
             'sendTime' => 1111111111,
             'status' => 'created',
         );

        $this->getProductOrderService()->createProductOrder($fields);

        $results = $this->getProductOrderService()->searchProductOrders(array(), array(), 0, PHP_INT_MAX);
        $this->assertEquals(1, count($results));

        $results = $this->getProductOrderService()->searchProductOrders(
             array('userId' => 2),
             array(),
             0,
             PHP_INT_MAX
         );

        $this->assertEquals(0, count($results));
    }

    public function testFindProductOrdersByUserId()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => '笔记本',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'edusoho@howzhi.com',
            'address' => '越源大厦',
            'sendTime' => 1111111111,
            'status' => 'created',
        );
        $createProductOrder = array();
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $result = $this->getProductOrderService()->findProductOrdersByUserId(1);
        $this->assertEquals(array($createProductOrder[0],$createProductOrder[1]), $result);
    }

    public function testFindProductOrdersByProductId()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => '笔记本',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'edusoho@howzhi.com',
            'address' => '越源大厦',
            'sendTime' => 1111111111,
            'status' => 'created',
        );
        $createProductOrder = array();
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $result = $this->getProductOrderService()->findProductOrdersByProductId(1);

        $this->assertEquals(array($createProductOrder[0],$createProductOrder[1]), $result);
    }

    protected function getProductOrderService()
    {
        return $this->createService('RewardPointProduct:ProductOrderService');
    }
}
