<?php

namespace MarketingMallBundle\Event;

use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use MarketingMallBundle\Client\MarketingMallClient;
use MarketingMallBundle\Common\GoodsContentBuilder\AbstractBuilder;

abstract class BaseEventSubscriber extends EventSubscriber
{
    protected function updateGoodsContent($type, AbstractBuilder $builder, $id)
    {
        $relation = $this->getProductMallGoodsRelationService()->getProductMallGoodsRelationByProductTypeAndProductId($type, $id);

        if (empty($relation)) {
            if ($type == 'course' && !$this->getProductMallGoodsRelationService()->checkMallClassroomCourseExist($id)) {
                return;
            }
            if ($type != 'course') {
                return;
            }
        }
        $builder->setBiz($this->getBiz());
        $client = new MarketingMallClient($this->getBiz());
        $client->updateGoodsContent([
            'targetType' => $type,
            'goodsContent' => json_encode($builder->build($id)),
        ]);
    }

    public function updateTeacherInfo(AbstractBuilder $builder, $id)
    {
        $builder->setBiz($this->getBiz());
        $client = new MarketingMallClient($this->getBiz());
        $client->updateTeacherInfo([
            'content' => json_encode($builder->build($id)),
        ]);
    }

    protected function deleteMallGoods($code)
    {
        try {
            $client = new MarketingMallClient($this->getBiz());
            $result = $client->deleteGoodsBycode($code);
            if (!$result['ok']) {
                throw new ServiceException('删除营销商城商品失败，请重试！');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    protected function getProductMallGoodsRelationService()
    {
        return $this->getBiz()->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
