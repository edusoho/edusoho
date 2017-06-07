<?php

namespace Tests\Unit\RewardPoint;

use Biz\BaseTestCase;

class ProductOrderServiceTest extends BaseTestCase
{
    public function testCreateProductOrder()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
            'sendTime' => 1111111111,
            'status' => 'created',
        );

        $result = $this->getProductOrderService()->createProductOrder($fields);
        $this->assertEquals($fields['userId'], $result['userId']);
        $this->assertEquals($fields['productId'], $result['productId']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateProductOrderWithoutProductOrder()
    {
        $this->getProductOrderService()->createProductOrder(array('userId' => 2));
    }

    public function testDeleteProductOrder()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
            'sendTime' => 1111111111,
            'status' => 'created',
        );

        $createProductOrder = $this->getProductOrderService()->createProductOrder($fields);
        $this->getProductOrderService()->deleteProductOrder($createProductOrder['id']);
        $deleted = $this->getProductOrderService()->getProductOrder($createProductOrder['id']);
        $this->assertEmpty($deleted);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testDeleteProductOrderWithoutProductOrder()
    {
        $this->getProductOrderService()->deleteProductOrder(8888);
    }

    public function testUpdateProductOrder()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
            'sendTime' => 1111111111,
            'status' => 'created',
        );

        $createProductOrder = $this->getProductOrderService()->createProductOrder($fields);
        $updateRecord = $this->getProductOrderService()->updateProductOrder(
             $createProductOrder['id'],
             array('title' => 'computer')
         );

        $this->assertEquals('computer', $updateRecord['title']);
        $this->assertEquals($createProductOrder['id'], $updateRecord['id']);
    }

    public function testGetProductOrder()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
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
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
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
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
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
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
            'sendTime' => 1111111111,
            'status' => 'created',
        );

        $createProductOrder = array();
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $result = $this->getProductOrderService()->findProductOrdersByUserId(1);
        $this->assertEquals(array($createProductOrder[0], $createProductOrder[1]), $result);
    }

    public function testFindProductOrdersByProductId()
    {
        $fields = array(
            'sn' => '1010',
            'productId' => 1,
            'title' => 'book',
            'price' => 454,
            'userId' => 1,
            'telephone' => '16732147311',
            'email' => 'test@kz.com',
            'address' => 'testAddress',
            'sendTime' => 1111111111,
            'status' => 'created',
        );
        $createProductOrder = array();
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $createProductOrder[] = $this->getProductOrderService()->createProductOrder($fields);
        $result = $this->getProductOrderService()->findProductOrdersByProductId(1);

        $this->assertEquals(array($createProductOrder[0], $createProductOrder[1]), $result);
    }

    protected function getProductOrderService()
    {
        return $this->createService('RewardPoint:ProductOrderService');
    }
}
