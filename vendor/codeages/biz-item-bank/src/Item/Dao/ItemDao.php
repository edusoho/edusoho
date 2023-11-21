<?php

namespace Codeages\Biz\ItemBank\Item\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ItemDao extends AdvancedDaoInterface
{
    public function findByIds($ids);

    public function findByCategoryIds($categoryIds);

    public function getItemCountGroupByTypes($conditions);

    public function countItemQuestionNumByBankId($bankId);

    public function countItemQuestionNumByCategoryId($categoryId);

    public function findMaterialByMaterialHashes($bankId, $materialHashes);

    public function findDuplicatedMaterialHashes($bankId, $categoryId = 0);

    public function findDuplicatedMaterials($bankId, array $materialHashes);
}
