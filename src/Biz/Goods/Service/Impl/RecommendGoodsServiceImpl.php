<?php

namespace Biz\Goods\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Goods\Dao\GoodsDao;
use Biz\Goods\Service\GoodsService;
use Biz\Goods\Service\RecommendGoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class RecommendGoodsServiceImpl extends BaseService implements RecommendGoodsService
{
    const MAX_SHOW_RECOMMENDED_GOODS_NUMBER = 6;

    public function findRecommendedGoodsByGoods($goods)
    {
        $goodsSetting = $this->getSettingService()->get('goods_setting', []);
        if (empty($goodsSetting['recommend_rule'])) {
            return [];
        }

        if ('hot' === $goodsSetting['recommend_rule']) {
            return $this->findRecommendedGoodsByHotSeq($goods);
        }

        if ('latest' === $goodsSetting['recommend_rule']) {
            return $this->findRecommendedGoodsByPublishTime($goods);
        }

        if ('label' === $goodsSetting['recommend_rule']) {
            return $this->findRecommendedGoodsByTag($goods);
        }

        return [];
    }

    public function refreshGoodsHotSeqByProductTypeAndProductMemberCount($productType, $productMemberCount)
    {
        $columnName = 'course' === $productType ? 'courseSetId' : 'classroomId';
        $products = $this->getProductService()->findProductsByTargetTypeAndTargetIds(
            $productType,
            ArrayToolkit::column($productMemberCount, $columnName)
        );

        $productsHotSeq = $this->appendProductsHotSeq($products, $productMemberCount);

        $batchHelper = new BatchUpdateHelper($this->getGoodsDao());
        foreach ($productsHotSeq as $product) {
            $batchHelper->add('productId', $product['productId'], ['hotSeq' => $product['hotSeq']]);
        }
        $batchHelper->flush();
    }

    protected function findRecommendedGoodsByHotSeq($goods)
    {
        return $this->getGoodsService()->searchGoods(
            [
                'type' => $goods['type'],
                'status' => 'published',
                'excludeId' => $goods['id'],
            ],
            ['hotSeq' => 'DESC', 'id' => 'DESC'],
            0,
            self::MAX_SHOW_RECOMMENDED_GOODS_NUMBER
        );
    }

    protected function findRecommendedGoodsByPublishTime($goods)
    {
        return $this->getGoodsService()->searchGoods(
            [
                'type' => $goods['type'],
                'status' => 'published',
                'excludeId' => $goods['id'],
            ],
            ['publishedTime' => 'DESC', 'id' => 'DESC'],
            0,
            self::MAX_SHOW_RECOMMENDED_GOODS_NUMBER
        );
    }

    protected function findRecommendedGoodsByTag($goods)
    {
        $product = $this->getProductService()->getProduct($goods['productId']);
        if (empty($product)) {
            return [];
        }

        $tagOwnerType = $this->getTagOwnerTypeByProductType($product['targetType']);
        if (empty($tagOwnerType)) {
            return [];
        }

        $tags = $this->getTagService()->findTagsByOwner(['ownerType' => $tagOwnerType, 'ownerId' => $product['targetId']]);
        if (empty($tags)) {
            return [];
        }

        $currentGoodsTagIds = array_column($tags, 'id');
        $ownerIds = $this->getTagService()->findDistinctOwnerIdByOwnerTypeAndTagIdsAndExcludeOwnerId(
            $tagOwnerType,
            $currentGoodsTagIds,
            $product['targetId'],
            self::MAX_SHOW_RECOMMENDED_GOODS_NUMBER
        );

        $products = $this->getProductService()->findProductsByTargetTypeAndTargetIds(
            $product['targetType'],
            ArrayToolkit::column($ownerIds, 'ownerId')
        );

        $productIds = ArrayToolkit::column($products, 'id');
//        if (self::MAX_SHOW_RECOMMENDED_GOODS_NUMBER == count($productIds)) {
//            return $this->getGoodsService()->findGoodsByProductIds($productIds);
//        }
//
//        $otherType = 'course' == $product['targetType'] ? 'classroom' : 'course';
//
//        $otherTypeOwnerIds = $this->getTagService()->findDistinctOwnerIdByOwnerTypeAndTagIdsAndExcludeOwnerId(
//            $otherType,
//            $currentGoodsTagIds,
//            0,
//            self::MAX_SHOW_RECOMMENDED_GOODS_NUMBER - count($ownerIds)
//        );
//
//        $otherProducts = $this->getProductService()->findProductsByTargetTypeAndTargetIds(
//            $otherType,
//            ArrayToolkit::column($otherTypeOwnerIds, 'ownerId'));
//
//        $productIds = array_merge($productIds, ArrayToolkit::column($otherProducts, 'id'));

        return $this->getGoodsService()->findGoodsByProductIds($productIds);
    }

    private function getTagOwnerTypeByProductType($productType)
    {
        $map = [
            'course' => 'course-set',
            'classroom' => 'classroom',
        ];

        return empty($map[$productType]) ? '' : $map[$productType];
    }

    private function appendProductsHotSeq(array $products, array $productStudentsCount)
    {
        if (empty($products) || empty($productStudentsCount)) {
            return [];
        }

        $buildProducts = [];
        foreach ($products as $product) {
            $currentProductTargetId = $product['targetId'];

            if (!empty($courseStudentsCount[$currentProductTargetId])) {
                $buildProducts[] = [
                    'productId' => $product['id'],
                    'hotSeq' => $productStudentsCount[$currentProductTargetId]['count'],
                ];
            }
        }

        return $buildProducts;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }
}
