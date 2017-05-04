<?php

namespace Biz\Accessor;

interface AccessorInterface
{
    const SUCCESS = 'success';

    /**
     * 对业务对象（bean）进行校验
     *
     * @param $bean
     *
     * @return array result with code and msg, null if pass
     */
    public function access($bean);
}
