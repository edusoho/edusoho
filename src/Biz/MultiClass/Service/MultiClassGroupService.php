<?php

namespace Biz\MultiClass\Service;

interface MultiClassGroupService
{
    public function findGroupsByMultiClassId($multiClassId);

    public function getById($id);
}
