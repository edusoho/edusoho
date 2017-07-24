<?php

namespace Tests\RewardPoint;

use Biz\BaseTestCase;

class ProductServiceTest extends BaseTestCase
{
    public function testCreateProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $addRewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $this->assertEquals('RewardPointProduct', $addRewardPointProduct['title']);
    }

    public function testCreateProductWithoutRequireTelephone()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $addRewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $this->assertEquals('0', $addRewardPointProduct['requireTelephone']);
    }

    public function testUpdateProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProductName_BeforeChange');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $updateRewardPointProduct = array('title' => 'RewardPointProductName_AfterChange', 'price' => '10', 'img' => 'files://default/test.png', 'about' => 'I am the rewardPointProduct test!');
        $updatedRewardPointProduct = $this->getRewardPointProductService()->updateProduct($rewardPointProduct['id'], $updateRewardPointProduct);
        $this->assertEquals('RewardPointProductName_AfterChange', $updatedRewardPointProduct['title']);
    }

    public function testUpdateProductWithoutRequireConsignee()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct', 'requireConsignee' => '1');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $updateRewardPointProduct = array('title' => 'RewardPointProductName_AfterChange', 'price' => '10', 'img' => 'files://default/test.png', 'about' => 'I am the rewardPointProduct test!');
        $updatedRewardPointProduct = $this->getRewardPointProductService()->updateProduct($rewardPointProduct['id'], $updateRewardPointProduct);
        $this->assertEquals('0', $updatedRewardPointProduct['requireConsignee']);
    }

    public function testUpShelves()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $upRewardPointProduct = $this->getRewardPointProductService()->upShelves($rewardPointProduct['id']);
        $this->assertEquals('published', $upRewardPointProduct['status']);
    }

    public function testDownShelves()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct', 'status' => 'published');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $downRewardPointProduct = $this->getRewardPointProductService()->downShelves($rewardPointProduct['id']);
        $this->assertEquals('draft', $downRewardPointProduct['status']);
    }

    public function testDeleteProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $deletedRewardPointProduct = $this->getRewardPointProductService()->deleteProduct($rewardPointProduct['id']);
        $this->assertEquals(1, $deletedRewardPointProduct);
    }

    public function testGetProduct()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $rewardPointProduct = $this->createRewardPointProduct($rewardPointProduct);
        $foundRewardPointProduct = $this->getRewardPointProductService()->getProduct($rewardPointProduct['id']);
        $this->assertEquals('RewardPointProduct', $foundRewardPointProduct['title']);
    }

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

    public function testCountProducts()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $this->createRewardPointProduct($rewardPointProduct);
        $foundRewardPointProduct = $this->getRewardPointProductService()->countProducts(array());
        $this->assertEquals(1, $foundRewardPointProduct);
    }

    public function testSearchProducts()
    {
        $rewardPointProduct = array('title' => 'RewardPointProduct');
        $this->createRewardPointProduct($rewardPointProduct);
        $foundRewardPointProduct = $this->getRewardPointProductService()->searchProducts(array(), array(), 0, 2);
        $this->assertCount(1, $foundRewardPointProduct);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpdateProductWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->updateProduct($id, array());
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpShelvesWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->upShelves($id);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testDownShelvesWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->downShelves($id);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testDeleteProductWithoutProduct()
    {
        $id = '';
        $this->getRewardPointProductService()->deleteProduct($id);
    }

    private function createRewardPointProduct($rewardPointProduct)
    {
        $requiredFileds = array('price' => '10', 'img' => 'files://default/test.png', 'about' => 'I am the rewardPointProduct test!');
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
