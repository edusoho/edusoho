<?php

namespace Mooc\Service\Course\Impl;

use Topxia\Service\Course\Impl\ThreadServiceImpl as BaseThreadServiceImpl;

class ThreadServiceImpl extends BaseThreadServiceImpl
{
    public function searchThreadPostCount($conditions)
    {
        if (empty($conditions)) {
            return array();
        }

        return $this->getThreadPostDao()->searchThreadPostCount($conditions);
    }
}
