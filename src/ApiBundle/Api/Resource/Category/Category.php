<?php

namespace ApiBundle\Api\Resource\Category;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Taxonomy\CategoryException;
use Biz\Taxonomy\Service\CategoryService;

class Category extends AbstractResource
{
    private $allowedGroupCodes = [
        'course', 'classroom', 'itemBankExercise',
    ];

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $groupCode)
    {
        if (!in_array($groupCode, $this->allowedGroupCodes)) {
            throw CommonException::ERROR_PARAMETER();
        }
        if ('itemBankExercise' == $groupCode) {
            return $this->getQuestionBankCategoryService()->getCategoryStructureTree();
        }
        $group = $this->getCategoryService()->getGroupByCode($groupCode);
        if (!$group) {
            throw CategoryException::NOTFOUND_GROUP();
        }

        return $this->getCategoryService()->getCategoryStructureTree($group['id']);
    }

    /**
     * @return CategoryService
     */
    private function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\QuestionBank\Service\CategoryService
     */
    protected function getQuestionBankCategoryService()
    {
        return $this->service('QuestionBank:CategoryService');
    }
}
