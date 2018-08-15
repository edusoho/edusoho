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
            'title' => '所有课程'
            array(
                'type' => 'category',
                'moduleType' => 'tree',
                'text' => '分类',
                'data' => $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0),
            ),
            array(
                'type' => 'courseType',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'normal',
                        'text' => '课程',
                    ),
                    array(
                        'type' => 'live',
                        'text' => '直播',
                    ),
                ),
            ),
            array(
                'type' => 'sort',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'recommendedSeq',
                        'text' => '推荐',
                    ),
                    array(
                        'type' => 'hitNum',
                        'text' => '热门',
                    ),
                    array(
                        'type' => 'createdTime',
                        'text' => '最新',
                    ),
                ),
            ),
        );
    }

    protected function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }
}
