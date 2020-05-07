<?php

namespace Codeages\Biz\ItemBank\ItemBank\Service\Impl;

use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\ItemBank\Dao\ItemBankDao;
use Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;

class ItemBankServiceImpl extends BaseService implements ItemBankService
{
    public function createItemBank($itemBank)
    {
        $itemBank = $this->getValidator()->validate($itemBank, [
            'name' => ['required'],
        ]);
        $itemBank['created_user_id'] = $itemBank['updated_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];

        return $this->getItemBankDao()->create($itemBank);
    }

    public function updateItemBank($id, $itemBank)
    {
        if (empty($this->getItemBank($id))) {
            throw new ItemBankException('Item bank is not found.', ErrorCode::ITEM_BANK_NOT_FOUND);
        }

        $itemBank = $this->getValidator()->validate($itemBank, [
            'name' => ['required'],
        ]);
        $itemBank['updated_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];

        return $this->getItemBankDao()->update($id, $itemBank);
    }

    public function getItemBank($id)
    {
        return $this->getItemBankDao()->get($id);
    }

    public function searchItemBanks($conditions, $orderBys, $start, $limit)
    {
        return $this->getItemBankDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countItemBanks($conditions)
    {
        return $this->getItemBankDao()->count($conditions);
    }

    public function deleteItemBank($id)
    {
        $itemBank = $this->getItemBank($id);
        if (empty($itemBank)) {
            throw new ItemBankException('Item bank is not found.', ErrorCode::ITEM_BANK_NOT_FOUND);
        }

        if ($itemBank['assessment_num'] > 0 || $itemBank['item_num'] > 0) {
            throw new ItemBankException('The item bank has assessment or items.', ErrorCode::ITEM_BANK_NOT_EMPTY);
        }

        $this->getItemBankDao()->delete($id);
    }

    public function updateAssessmentNum($id, $diff)
    {
        return $this->getItemBankDao()->wave([$id], ['assessment_num' => $diff]);
    }

    public function updateItemNum($id, $diff)
    {
        return $this->getItemBankDao()->wave([$id], ['item_num' => $diff]);
    }

    /**
     * @return ItemBankDao
     */
    public function getItemBankDao()
    {
        return $this->biz->dao('ItemBank:ItemBank:ItemBankDao');
    }
}
