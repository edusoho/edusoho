<?php

namespace Tests\ItemBank\Service;

use Codeages\Biz\ItemBank\ItemBank\Dao\ItemBankDao;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;
use Tests\IntegrationTestCase;

class ItemBankServiceTest extends IntegrationTestCase
{
    public function testCreateItemBank()
    {
        $itemBank = $this->getItemBankService()->createItemBank(['name' => 'default']);
        $itemBank = $this->getItemBankDao()->get($itemBank['id']);

        $this->assertEquals('default', $itemBank['name']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testCreateItemBank_ArgumentInvalid_ThrowException()
    {
        $this->getItemBankService()->createItemBank([]);
    }

    public function testUpdateItemBank()
    {
        $itemBank = $this->createItemBank(['name' => 'default']);
        $itemBank = $this->getItemBankService()->updateItemBank($itemBank['id'], ['name' => 'maths']);
        $itemBank = $this->getItemBankDao()->get($itemBank['id']);
        $this->assertEquals('maths', $itemBank['name']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_FOUND
     */
    public function testUpdateNotExistItemBank_ThrowException()
    {
        $this->getItemBankService()->updateItemBank(1, ['name' => 'maths']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testUpdateItemBank_ArgumentInvalid_ThrowException()
    {
        $itemBank = $this->createItemBank(['name' => 'default']);
        $this->getItemBankService()->updateItemBank($itemBank['id'], []);
    }

    public function testGetItemBank()
    {
        $itemBank = $this->createItemBank(['name' => 'default']);
        $itemBank = $this->getItemBankService()->getItemBank($itemBank['id']);

        $this->assertEquals('default', $itemBank['name']);
    }

    public function testSearchItemBanks()
    {
        $this->createItemBank(['name' => 'c++']);
        $this->createItemBank(['name' => 'c#']);
        $this->createItemBank(['name' => 'java']);
        $this->createItemBank(['name' => 'javascript']);

        $items = $this->getItemBankService()->searchItemBanks(['nameLike' => 'java'], ['id' => 'ASC'], 0, PHP_INT_MAX);
        $this->assertEquals(2, count($items));
    }

    public function testCountItemBanks()
    {
        $this->createItemBank(['name' => 'c']);
        $this->createItemBank(['name' => 'go']);
        $this->createItemBank(['name' => 'rust']);
        $this->createItemBank(['name' => 'ruby']);

        $itemCount = $this->getItemBankService()->countItemBanks(['nameLike' => 'c']);
        $this->assertEquals(1, $itemCount);
        $itemCount = $this->getItemBankService()->countItemBanks(['nameLike' => 'ru']);
        $this->assertEquals(2, $itemCount);
    }

    public function testDeleteItemBank()
    {
        $item = $this->createItemBank(['name' => 'default']);
        $item = $this->getItemBankDao()->get($item['id']);
        $this->assertEquals('default', $item['name']);

        $this->getItemBankService()->deleteItemBank($item['id']);
        $item = $this->getItemBankDao()->get($item['id']);
        $this->assertEmpty($item);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_FOUND
     */
    public function testDeleteNotExistItemBank_ThrowException()
    {
        $this->getItemBankService()->deleteItemBank(1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_BANK_NOT_EMPTY
     */
    public function testDeleteNotEmptyItemBank_ThrowException()
    {
        $itemBank = $this->createItemBank(['name' => 'default']);
        $this->getItemBankDao()->wave([$itemBank['id']], ['item_num' => 1]);

        $this->getItemBankService()->deleteItemBank($itemBank['id']);
    }

    public function testUpdateAssessmentNum()
    {
        $itemBank = $this->createItemBank(['name' => 'default']);
        $this->getItemBankService()->updateAssessmentNum($itemBank['id'], 3);
        $itemBank = $this->getItemBankDao()->get($itemBank['id']);
        $this->assertEquals(3, $itemBank['assessment_num']);
        $this->getItemBankService()->updateAssessmentNum($itemBank['id'], -1);
        $itemBank = $this->getItemBankDao()->get($itemBank['id']);
        $this->assertEquals(2, $itemBank['assessment_num']);
    }

    public function testUpdateItemNumAndQuestionNum()
    {
        $itemBank = $this->createItemBank(['name' => 'default']);
        $this->getItemBankService()->updateItemNumAndQuestionNum($itemBank['id']);
        $itemBank = $this->getItemBankDao()->get($itemBank['id']);
        $this->assertEquals(0, $itemBank['question_num']);
        $this->assertEquals(0, $itemBank['item_num']);
    }

    protected function createItemBank($itemBank)
    {
        $itemBank['created_user_id'] = 1;
        $itemBank['updated_user_id'] = 1;

        return $this->getItemBankDao()->create($itemBank);
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->biz->service('ItemBank:ItemBank:ItemBankService');
    }

    /**
     * @return ItemBankDao
     */
    protected function getItemBankDao()
    {
        return $this->biz->dao('ItemBank:ItemBank:ItemBankDao');
    }
}
