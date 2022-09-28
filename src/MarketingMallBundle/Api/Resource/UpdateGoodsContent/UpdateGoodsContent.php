<?php


namespace MarketingMallBundle\Api\Resource\UpdateGoodsContent;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use MarketingMallBundle\Api\Resource\UpdateGoodsContentApi;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use MarketingMallBundle\Common\GoodsContentBuilder\AbstractBuilder;
use MarketingMallBundle\Common\GoodsContentBuilder\ClassroomInfoBuilder;
use MarketingMallBundle\Common\GoodsContentBuilder\CourseInfoBuilder;
use MarketingMallBundle\Common\GoodsContentBuilder\QuestionBankBuilder;

class UpdateGoodsContent extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $type = $request->query->get('type');
        $ids = explode(',',$request->query->get('ids'));
//        $ids = $request->query->get('ids');

        switch ($type) {
            case 'course':
                return $this->updateGoodsContent(new CourseInfoBuilder(),$type,$ids);
                break;
            case 'classroom':
                return $this->updateGoodsContent(new ClassroomInfoBuilder(),$type,$ids);
                break;
            case 'questionBank':
                return $this->updateGoodsContent(new QuestionBankBuilder(),$type,$ids);
                break;
            default:
                break;
        }
    }

    protected  function updateGoodsContent(AbstractBuilder $builder, $type, $ids)
    {
        $builder->setBiz($this->getBiz());
        return $builder->build($ids);

    }

    /**
     * @return ProductMallGoodsRelationService
     */
    protected function getProductMallGoodsRelationService()
    {
        return $this->getBiz()->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}