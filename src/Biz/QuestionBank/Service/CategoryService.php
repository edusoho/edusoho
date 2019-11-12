<?php

namespace Biz\QuestionBank\Service;

interface CategoryService
{
    public function getCategory($id);

    public function getCategoryStructureTree();

    public function getCategoryTree();

    public function findAllCategories();
}
