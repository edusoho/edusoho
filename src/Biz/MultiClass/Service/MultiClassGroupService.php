<?php

namespace Biz\MultiClass\Service;

interface MultiClassGroupService
{
    public function findByIds($ids);

    public function findGroupsByMultiClassId($multiClassId);
}
