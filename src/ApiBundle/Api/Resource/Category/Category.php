<?php

namespace ApiBundle\Api\Resource\Category;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Taxonomy\Service\CategoryService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Category extends AbstractResource
{
    private $allowedGroupCodes = array(
        'course', 'classroom',
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $groupCode)
    {
        if (!in_array($groupCode, $this->allowedGroupCodes)) {
            throw new BadRequestHttpException('The code is Illegal', null, ErrorCode::INVALID_ARGUMENT);
        }

        $group = $this->getCategoryService()->getGroupByCode($groupCode);
        if (!$group) {
            throw new NotFoundHttpException('The group not found', null, ErrorCode::RESOURCE_NOT_FOUND);
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
}
