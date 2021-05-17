<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function findByProductId($productId);

    public function getMultiClassByTitle($title);
}
