<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function createMultiClass($fields);

    public function getMultiClassByTitle($title);
}
