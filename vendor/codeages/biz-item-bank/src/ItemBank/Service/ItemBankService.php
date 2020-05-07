<?php

namespace Codeages\Biz\ItemBank\ItemBank\Service;

interface ItemBankService
{
    public function createItemBank($itemBank);

    public function updateItemBank($id, $itemBank);

    public function getItemBank($id);

    public function searchItemBanks($conditions, $orderBys, $start, $limit);

    public function countItemBanks($conditions);

    public function deleteItemBank($id);

    public function updateAssessmentNum($id, $diff);

    public function updateItemNum($id, $diff);
}
