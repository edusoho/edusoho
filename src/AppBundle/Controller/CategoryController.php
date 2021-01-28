<?php

namespace AppBundle\Controller;

use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends BaseController
{
    public function allAction()
    {
        $categories = $this->getCategoryService()->findCategories(1);

        $data = array();
        foreach ($categories as $category) {
            $data[$category['id']] = array($category['name'], $category['parentId']);
        }

        return $this->createJsonResponse($data);
    }

    protected function makeCategories($group)
    {
        if (in_array($group, array('course', 'open_course'))) {
            $group = 'course';
        }

        $group = $this->getCategoryService()->getGroupByCode($group);

        if (empty($group)) {
            return array();
        }

        $categories = $this->getCategoryService()->getCategoryTree($group['id']);

        foreach ($categories as $id => $category) {
            if ('0' != $categories[$id]['parentId']) {
                unset($categories[$id]);
            }
        }

        return $categories;
    }

    protected function makeTags()
    {
        $tagGroups = $this->getTagService()->findTagGroups();

        foreach ($tagGroups as $key => $tagGroup) {
            $allTags = $this->getTagService()->findTagsByGroupId($tagGroup['id']);
            $tagGroups[$key]['subs'] = $allTags;
        }

        return $tagGroups;
    }

    protected function makeSubCategories($category)
    {
        $subCategories = array();

        if (empty($category)) {
            return $subCategories;
        }

        $categoryArray = $this->getCategoryService()->getCategoryByCode($category);

        if (!empty($categoryArray) && 0 == $categoryArray['parentId']) {
            $subCategories = $this->getCategoryService()->findAllCategoriesByParentId($categoryArray['id']);
        }

        if (!empty($categoryArray) && 0 != $categoryArray['parentId']) {
            $subCategories = $this->getCategoryService()->findAllCategoriesByParentId($categoryArray['parentId']);
        }

        return $subCategories;
    }

    protected function makeThirdCategories($selectedSubCategory)
    {
        $thirdCategories = array();

        if (empty($selectedSubCategory)) {
            return $thirdCategories;
        }

        $parentCategory = $this->getCategoryService()->getCategoryByCode($selectedSubCategory);

        if (empty($parentCategory)) {
            return $thirdCategories;
        }

        return $this->getCategoryService()->findAllCategoriesByParentId($parentCategory['id']);
    }

    public function treeNavAction(Request $request, $category, $tags, $group = 'course')
    {
        $selectedSubCategory = $request->query->get('subCategory', '');
        $thirdLevelCategory = $request->query->get('selectedthirdLevelCategory', '');

        $categories = $this->makeCategories($group);
        $tagGroups = $this->makeTags();

        $subCategories = $this->makeSubCategories($category);

        $thirdLevelCategories = $this->makeThirdCategories($selectedSubCategory);

        return $this->render('category/explore-nav.html.twig', array(
            'selectedCategory' => $category,
            'selectedSubCategory' => $selectedSubCategory,
            'selectedthirdLevelCategory' => $thirdLevelCategory,
            'thirdLevelCategories' => $thirdLevelCategories,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'tagGroups' => $tagGroups,
            'tags' => $tags,
            'group' => $group,
            'request' => $request,
        ));
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }
}
