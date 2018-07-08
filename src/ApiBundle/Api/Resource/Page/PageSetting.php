<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Exception\ErrorCode;

class PageSetting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $type)
    {
        if (!in_array($type, array('course'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        $method = "get${type}";

        return $this->$method();
    }

    protected function getCourse()
    {
        $group = $this->getCategoryService()->getGroupByCode('course');

        return array(
            'categories' => $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0),
            'courseType' => array(
                'normal' => '课程',
                'live' => '直播',
            ),
            'sort' => array(
                'recommendedSeq' => '推荐',
                'hitNum' => '热门',
                'createdTime' => '最新',
            ),
        );
    }

    protected function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }
}
