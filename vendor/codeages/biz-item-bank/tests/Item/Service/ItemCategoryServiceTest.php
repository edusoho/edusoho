<?php

namespace Tests\Item\Service;

use Codeages\Biz\ItemBank\Item\Dao\ItemCategoryDao;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Tests\IntegrationTestCase;

class ItemCategoryServiceTest extends IntegrationTestCase
{
    public function testCreateItemCategory()
    {
        $this->mockItemBankService();
        $category = $this->getItemCategoryService()->createItemCategory([
            'name' => 'default',
            'parent_id' => 0,
            'bank_id' => 1,
        ]);
        $category = $this->getItemCategoryDao()->get($category['id']);
        $this->assertEquals('default', $category['name']);
        $this->assertEquals(1, $category['bank_id']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_FOUND
     */
    public function testCreateItemCategory_BankNotExist_ThrowException()
    {
        $this->getItemCategoryService()->createItemCategory([
            'name' => 'default',
            'parent_id' => 0,
            'bank_id' => 1,
        ]);
    }

    public function testCreateItemCategories()
    {
        $this->mockItemBankService();
        $this->getItemCategoryService()->createItemCategories(1, 0, ['c++', 'c', 'go', 'rust']);
        $categories = $this->getItemCategoryDao()->findByBankId(1);

        $this->assertEquals(4, count($categories));
    }

    public function testCreateItemCategories_EmptyName_ReturnEmpty()
    {
        $this->mockItemBankService();
        $result = $this->getItemCategoryService()->createItemCategories(1, 0, []);

        $this->assertEmpty($result);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_FOUND
     */
    public function testCreateItemCategories_BankNotExist_ThrowException()
    {
        $this->getItemCategoryService()->createItemCategories(1, 0, ['c++', 'c', 'go', 'rust']);
    }

    public function testUpdateItemCategory()
    {
        $this->mockItemBankService();
        $this->initData();

        $category = $this->getItemCategoryService()->updateItemCategory(1, ['name' => 'maths']);
        $this->assertEquals('maths', $category['name']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemCategoryException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_CATEGORY_NOT_FOUND
     */
    public function testUpdateItemCategory_NotExist_ThrowException()
    {
        $this->getItemCategoryService()->updateItemCategory(1, []);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testUpdateItemCategory_ArgumentInvalid_ThrowException()
    {
        $this->mockItemBankService();
        $this->initData();

        $this->getItemCategoryService()->updateItemCategory(1, []);
    }

    public function testGetItemCategory()
    {
        $this->mockItemBankService();
        $this->initData();

        $category = $this->getItemCategoryService()->getItemCategory(1);
        $this->assertEquals('pl', $category['name']);
        $this->assertEquals(1, $category['bank_id']);
    }

    public function testDeleteItemCategory()
    {
        $this->mockItemBankService();
        $this->initData();

        $category = $this->getItemCategoryDao()->get(1);
        $this->assertEquals('pl', $category['name']);
        $categories = $this->getItemCategoryDao()->findByBankId(1);
        $this->assertEquals(5, count($categories));
        $this->getItemCategoryService()->deleteItemCategory($category['id']);
        $category = $this->getItemCategoryDao()->get($category['id']);
        $this->assertEmpty($category);
        $categories = $this->getItemCategoryDao()->findByBankId(1);
        $this->assertEmpty($categories);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemCategoryException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_CATEGORY_NOT_FOUND
     */
    public function testDeleteItemCategory_CategoryNotExist_ThrowException()
    {
        $this->getItemCategoryService()->deleteItemCategory(1);
    }

    public function testFindItemCategoriesByIds()
    {
        $this->initData();
        $categories = $this->getItemCategoryService()->findItemCategoriesByIds([2, 3, 4, 5]);

        $this->assertEquals(4, count($categories));
    }

    public function testFindItemCategoriesByBankId()
    {
        $this->mockItemBankService();
        $this->initData();
        $categories = $this->getItemCategoryService()->findItemCategoriesByBankId(1);

        $this->assertEquals(5, count($categories));
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_FOUND
     */
    public function testFindItemCategoriesByBankId_BankNotExist_ThrowException()
    {
        $this->getItemCategoryService()->findItemCategoriesByBankId(1);
    }

    public function testGetItemCategoryTree()
    {
        $this->mockItemBankService();
        $this->initData();

        $categoryTree = $this->getItemCategoryService()->getItemCategoryTree(1);
        $this->assertEquals(1, count($categoryTree));
        $this->assertEquals(4, count($categoryTree[0]['children']));
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_FOUND
     */
    public function testGetItemCategoryTree_BankNotExist_ThrowException()
    {
        $this->getItemCategoryService()->getItemCategoryTree(1);
    }

    public function testFindCategoryChildrenIds()
    {
        $this->mockItemBankService();
        $this->initData();

        $categoryChildrenIds = $this->getItemCategoryService()->findCategoryChildrenIds(1);
        $categories = $this->getItemCategoryDao()->findByIds($categoryChildrenIds);
        $categoryNames = array_column($categories, 'name');
        foreach ($categoryNames as $categoryName) {
            $this->assertTrue(in_array($categoryName, ['c++', 'c', 'go', 'rust']));
        }
    }

    protected function mockItemBankService()
    {
        $this->mockObjectIntoBiz('ItemBank:ItemBank:ItemBankService', [[
            'functionName' => 'getItemBank',
            'returnValue' => ['id' => 1],
        ]]);
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->biz->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return ItemCategoryDao
     */
    protected function getItemCategoryDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemCategoryDao');
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
