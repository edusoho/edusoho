<?php


namespace MarketingMallBundle\Event;


use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use MarketingMallBundle\Client\MarketingMallClient;

class ProjectDeleteEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'project.delete' => 'onProjectDelete',
        ];
    }

    public function onProjectDelete(Event $event)
    {
        try {
            $project = $event->getSubject();
            $relation = $this->getProductMallGoodsRelationService()->getProductMallGoodsRelationByProductTypeAndProductId($event->getArgument('type'), $project['id']);
            if ($relation) {
                $this->getProductMallGoodsRelationService()->deleteProductMallGoodsRelation($relation['id']);
                $client = new MarketingMallClient($this->getBiz());
                file_put_contents('/tmp/test', \GuzzleHttp\json_encode($client->deleteGoodsBycode($relation['goodsCode'])));
                $result = $client->deleteGoodsBycode('eq110');
                if (!$result['success']) {
                    throw new ServiceException('删除营销商城商品失败，请重试！');
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    private function getProductMallGoodsRelationService()
    {
        return $this->getBiz()->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}