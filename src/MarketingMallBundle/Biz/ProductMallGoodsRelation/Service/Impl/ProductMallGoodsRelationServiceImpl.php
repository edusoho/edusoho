<?php

namespace MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Dao\ProductMallGoodsRelationDao;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use MarketingMallBundle\Client\MarketingMallClient;

class ProductMallGoodsRelationServiceImpl extends BaseService implements ProductMallGoodsRelationService
{
    public function getProductMallGoodsRelationByGoodsCode($code)
    {
        return $this->getProductMallGoodsRelationDao()->getByGoodsCode($code);
    }

    public function createProductMallGoodsRelation($relation)
    {
        if (!ArrayToolkit::requireds($relation, ['productType', 'productId', 'goodsCode'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $relation = ArrayToolkit::parts($relation, ['productType', 'productId', 'goodsCode']);

        return $this->getProductMallGoodsRelationDao()->create($relation);
    }

    public function deleteProductMallGoodsRelation($id)
    {
        return $this->getProductMallGoodsRelationDao()->delete($id);
    }

    public function getProductMallGoodsRelationByProductTypeAndProductId($productType, $productId)
    {
        return $this->getProductMallGoodsRelationDao()->getByProductTypeAndProductId($productType, $productId);
    }

    public function findProductMallGoodsRelationsByProductType($productType)
    {
        return $this->getProductMallGoodsRelationDao()->findByProductType($productType);
    }

    public function findProductMallGoodsRelationsByProductIdsAndProductType($productIds, $productType)
    {
        return $this->getProductMallGoodsRelationDao()->search(['productIds' => $productIds, 'type' => $productType], [], 0, PHP_INT_MAX);
    }

    public function checkMallClassroomCourseExist($courseId)
    {
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1), 'id');
        $classroomIds = ArrayToolkit::column($this->getClassroomService()->findClassroomsByCoursesIds($courseIds), 'classroomId');
        $relations = $this->findProductMallGoodsRelationsByProductIdsAndProductType($classroomIds, 'classroom');
        if (!empty($relations)) {
            return true;
        }
        return false;
    }

    public function checkMallGoods(array $productIds, $type)
    {
        $relations = $this->findProductMallGoodsRelationsByProductIdsAndProductType($productIds, $type);
        if ($relations) {
            $client = new MarketingMallClient($this->biz);
            $result = $client->checkGoodsIsPublishByCodes(ArrayToolkit::column($relations, 'goodsCode'));
            file_put_contents('/tmp/test',\GuzzleHttp\json_encode($result));
            if (in_array(true, $result)) {
                return 'error';
            }
            return 'existent';
        }
        return 'nonexistent';
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ProductMallGoodsRelationDao
     */
    protected function getProductMallGoodsRelationDao()
    {
        return $this->createDao('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationDao');
    }
}
