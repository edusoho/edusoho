<?php

namespace MarketingMallBundle\Api\Resource\MallClassroom;

use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;

class MallClassroom extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['excludeStatus'] = 'draft';
        $conditions['showable'] = 1;
        list($offset, $limit) = $this->preparePageCondition($conditions);
        $sort = [
            'createdTime' => 'DESC',
        ];
        $relations = $this->getProductMallGoodsRelationService()->findProductMallGoodsRelationsByProductType('classroom');
        $conditions['excludeIds'] = ArrayToolkit::column($relations, 'productId');

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $sort,
            $offset,
            $limit,
            ['id', 'title', 'smallPicture', 'middlePicture', 'price', 'courseNum']
        );
        $total = $this->getClassroomService()->countClassrooms($conditions);

        return $this->makePagingObject($classrooms, $total, $offset, $limit);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    protected function getProductMallGoodsRelationService()
    {
        return $this->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
