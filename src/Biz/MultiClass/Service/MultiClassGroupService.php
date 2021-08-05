<?php

namespace Biz\MultiClass\Service;

interface MultiClassGroupService
{
    public function findGroupsByIds($ids);

    public function findGroupsByMultiClassId($multiClassId);
}
