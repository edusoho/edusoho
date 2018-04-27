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
     * @param $token course:1:333:123:1524238654:067c96087bcff8e48442cc4aade425c2:DhWQQui55pl5GrW3LAivhP-n13g=
     *
     * @return 返回路由需要的参数，如 array('id' => $id)
     */
    public function getRoutingParams($token);
}
