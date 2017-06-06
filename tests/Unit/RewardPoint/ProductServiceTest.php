<?php

namespace Tests\RewardPoint;

use Biz\BaseTestCase;

class ProductServiceTest extends BaseTestCase
{
    /**
     * @test
     */
    public function testCreateProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $addRewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $this->assertEquals('RewardPointProduct', $addRewardPointProduct['title']);
    }

    /**
     * @test
     */
    public function testUpdateProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProductName_BeforeChange');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $updateRewardPointProduct = array('title' => 'RewardPointProductName_AfterChange');
        $updatedRewardPointProduct = $this->getRewardPointProductService()->updateProduct($rewardPointProduct['id'], $updateRewardPointProduct);
        $this->assertEquals('RewardPointProductName_AfterChange', $updatedRewardPointProduct['title']);
    }

    /**
     * @test
     */
    public function testUpShelves()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $upRewardPointProduct = $this->getRewardPointProductService()->upShelves($rewardPointProduct['id']);
        $this->assertEquals('published', $upRewardPointProduct['status']);
    }

    /**
     * @test
     */
    public function testDownShelves()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct', 'status' => 'published');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $downRewardPointProduct = $this->getRewardPointProductService()->downShelves($rewardPointProduct['id']);
        $this->assertEquals('draft', $downRewardPointProduct['status']);
    }

    /**
     * @test
     */
    public function testDeleteProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $deletedRewardPointProduct = $this->getRewardPointProductService()->deleteProduct($rewardPointProduct['id']);
        $this->assertEquals(1, $deletedRewardPointProduct);
    }

    /**
     * @test
     */
    public function testGetProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $foundRewardPointProduct = $this->getRewardPointProductService()->getProduct($rewardPointProduct['id']);
        $this->assertEquals('RewardPointProduct', $foundRewardPointProduct['title']);
    }

    /**
     * @test
     */
    public function testFindProductsByIds()
    {
        $rewardPointProductA = array('title' => 'RewardPointProductA');
        $rewardPointProductB = array('title' => 'RewardPointProductB');
        $rewardPointProductA = $this->createRewardPointProduct($rewardPointProductA);
        $rewardPointProductB = $this->createRewardPointProduct($rewardPointProductB);
        $ids = array($rewardPointProductA['id'], $rewardPointProductB['id']);
        $rewardPointProducts = $this->getRewardPointProductService()->findProductsByIds($ids);
        $this->assertCount(2, $rewardPointProducts);
    }

    /**
     * @test
     */
    public function testCountProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $this->createRewardPointProduct($rewardPointProduct);
        $foundRewardPointProduct = $this->getRewardPointProductService()->countProduct(array());
        $this->assertEquals(1, $foundRewardPointProduct);
    }

    /**
     * @test
     */
    public function testSearchProducts()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $this->createRewardPointProduct($rewardPointProduct);
        $foundRewardPointProduct = $this->getRewardPointProductService()->searchProducts(array(), array(), 0, 2);
        $this->assertCount(1, $foundRewardPointProduct);
    }

    /**
     * @test
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpdateProductWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->updateProduct($id, array());
    }

    /**
     * @test
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpShelvesWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->upShelves($id);
    }

    /**
     * @test
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testDownShelvesWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->downShelves($id);
    }

    /**
     * @test
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testDeleteProductWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->deleteProduct($id);
    }

    private function createRewardPointProduct($rewardPointProduct)
    {
        $requiredFileds = array('price' => '10', 'requireTelephone' => '0', 'requireEmail' => '0', 'requireAddress' => '0');
        $fields = array_merge($requiredFileds, $rewardPointProduct);

        return $this->getRewardPointProductService()->createProduct($fields);
    }

    /**
     * @return RewardPointProductService
     */
    protected function getRewardPointProductService()
    {
        return $this->createService('RewardPoint:ProductService');
    }
}
