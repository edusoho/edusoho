<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Controller\BaseController;
use Biz\QuestionBank\Service\CategoryService;

class CategoryController extends BaseController
{
    public function treeNavAction($category)
    {
        $request = $this->get('request_stack')->getMasterRequest();
        $selectedSubCategory = $request->query->get('subCategory', '');
        $categories = $this->getCategoryService()->getCategoryStructureTree();
        $subCategories = $this->makeSubCategories($category);

        return $this->render('question-bank/category/tree-nav.html.twig', array(
            'selectedCategory' => $category,
            'selectedSubCategory' => $selectedSubCategory,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'request' => $request,
        ));
    }

    protected function makeSubCategories($category)
    {
        $subCategories = array();

        if (empty($category)) {
            return $subCategories;
        }

        $categoryArray = $this->getCategoryService()->getCategory($category);

        if (!empty($categoryArray) && 0 == $categoryArray['parentId']) {
            $subCategories = $this->getCategoryService()->findAllCategoriesByParentId($categoryArray['id']);
        }

        if (!empty($categoryArray) && 0 != $categoryArray['parentId']) {
            $subCategories = $this->getCategoryService()->findAllCategoriesByParentId($categoryArray['parentId']);
        }

        return $subCategories;
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }
}
