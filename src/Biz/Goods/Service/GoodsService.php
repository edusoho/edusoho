<?php

namespace Biz\Goods\Service;

interface GoodsService
{
    public function getGoods($id);

    public function createGoods($goods);

    public function updateGoods($id, $goods);

    public function updateGoodsMinAndMaxPrice($goodsId);

    public function freshGoodsSpecsCount($goodsId);

    public function deleteGoods($id);

    public function publishGoods($id);

    public function unpublishGoods($id);

    public function recommendGoods($id, $weight);

    public function cancelRecommendGoods($id);

    public function countGoods($conditions);

    public function searchGoods($conditions, $orderBys, $start, $limit, $columns = []);

    public function getGoodsByProductId($productId);

    public function changeGoodsMaxRate($id, $maxRate);

    public function hitGoods($id);

    public function createGoodsSpecs($goodsSpecs);

    public function getGoodsSpecs($id);

    public function updateGoodsSpecs($id, $goodsSpecs);

    public function changeGoodsSpecsPrice($id, $price);

    public function publishGoodsSpecs($id);

    public function unpublishGoodsSpecs($id);

    public function countGoodsSpecs($conditions);

    public function searchGoodsSpecs($conditions, $orderBys, $start, $limit, $columns = []);

    public function deleteGoodsSpecs($id);

    public function getGoodsSpecsByGoodsIdAndTargetId($goodsId, $targetId);

    public function findGoodsSpecsByGoodsId($goodsId);

    public function findPublishedGoodsSpecsByGoodsId($goodsId);

    public function getGoodsSpecsByProductIdAndTargetId($productId, $targetId);

    public function findGoodsByIds($ids);

    public function findGoodsByProductIds(array $productIds);

    public function refreshGoodsHotSeq();

    public function findGoodsSpecsByIds(array $ids);

    public function convertGoodsPrice($goods);

    public function convertSpecsPrice($goods, $specs);
}
