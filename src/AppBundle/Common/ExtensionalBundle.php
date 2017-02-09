<?php

namespace AppBundle\Common;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class ExtensionalBundle extends Bundle
{

    /**
     * 获得激活的扩展特性，可返回的值有：
     *     DataTag : 数据标签
     *     StatusTemplate : 个人动态模板
     *     DataDict : 数据字典
     *       
     * @return array 激活的特性数组
     */
    public function getEnabledExtensions()
    {
        return array();
    }

    public function getContainer()
    {
        return $this->container;
    }

}
