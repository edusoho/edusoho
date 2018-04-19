<?php

namespace Biz\Distributor\Service;

/**
 * 商品分销接口
 */
interface DistributorProductService
{
    /**
     * @return course_show
     */
    public function getRoutingName();

    /**
     * 返回路由需要的参数
     *
     * @return array('courseId' => $id)
     */
    public function getRoutingParams($id);
}
