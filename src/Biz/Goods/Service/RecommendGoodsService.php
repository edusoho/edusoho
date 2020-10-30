<?php

namespace Biz\Goods\Service;

interface RecommendGoodsService
{
    public function findRecommendedGoodsByGoods($goods);

    public function refreshGoodsHotSeqByProductTypeAndProductMemberCount($productType, $productMemberCount);
}
