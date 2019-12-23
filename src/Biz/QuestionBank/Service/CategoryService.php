<?php

namespace Biz\QuestionBank\Service;

use Biz\System\Annotation\Log;

interface CategoryService
{
    public function getCategory($id);

    public function findCategoriesByIds($ids);

    public function getCategoryStructureTree();

    /**
     * @param $category
     *
     * @return mixed
     * @Log(module="question_bank",action="create_category")
     */
    public function createCategory(array $category);

    /**
     * @param $id
     * @param array $fields
     *
     * @return mixed
     * @Log(module="question_bank",action="update_category",param="id")
     */
    public function updateCategory($id, array $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="question_bank",action="delete_category",funcName="getCategory")
     */
    public function deleteCategory($id);

    public function waveCategoryBankNum($id, $diff);

    public function getCategoryTree();

    public function getCategoryAndBankMixedTree();

    public function findAllCategories();

    public function findAllCategoriesByParentId($parentId);

    public function findAllChildrenIdsByParentId($parentId);

    public function sortCategories($ids);
}
