<?php

namespace MarketingMallBundle\Api\Resource\QuestionBankExercise;

use ApiBundle\Api\ApiRequest;
use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseService;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;

class QuestionBankExercise extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions['titleLike'])) {
            $conditions['title'] = $conditions['titleLike'];
            unset($conditions['titleLike']);
        }
        $conditions['excludeStatus'] = 'draft';
        $orderBys = ['createdTime' => 'DESC'];
        list($offset, $limit) = $this->preparePageCondition($conditions);
        $columns = [
            'id',
            'questionBankId',
            'title',
            'cover',
            'originPrice',
        ];

        $relations = $this->getProductMallGoodsRelationService()->findProductMallGoodsRelationsByProductType('questionBank');
        $conditions['excludeIds'] = ArrayToolkit::column($relations, 'productId');

        $exercise = $this->getExerciseService()->search($conditions, $orderBys, $offset, $limit, $columns);
        $total = $this->getExerciseService()->count($conditions);

        return $this->makePagingObject($exercise, $total, $offset, $limit);
    }

    /**
     * @return ExerciseService
     */
    private function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    protected function getProductMallGoodsRelationService()
    {
        return $this->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
