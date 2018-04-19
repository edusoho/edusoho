<?php

namespace Biz\Distributor\Service;

/**
 * 商品分销接口
 */
interface DistributorProductService
{
    /**
     * @return 路由，如course_show
     */
    public function getRoutingName();

    /**
     * @return 返回路由需要的参数，如 array('id' => $id)
     */
    public function getRoutingParams(Array $tokenInfo);
}
