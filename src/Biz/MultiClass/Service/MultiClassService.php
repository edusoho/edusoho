<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function findByProductIds($productIds);

    public function getMultiClassByTitle($title);
}
