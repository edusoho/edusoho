<?php

namespace AppBundle\Extensions\DataTag;

class CategoriesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取所有分类.
     *
     * 可传入的参数：
     *
     *   group      分类组CODE
     *   parentId   分类的父Id
     *
     * @param array $arguments 参数
     *
     * @return array 分类
     */
    public function getData(array $arguments)
    {
        $this->checkGroupId($arguments);

        $group = $this->getCategoryService()->getGroupByCode($arguments['group']);
        if (empty($group)) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('group:%argumentsGroup%不存在', array('%argumentsGroup%' => $arguments['group'])));
        }

        if (array_key_exists('parentId', $arguments)) {
            return $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], $arguments['parentId']);
        } else {
            return $this->getCategoryService()->findCategories($group['id']);
        }
    }
}
