<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function createMultiClass($multiClass);

    public function getMultiClassByTitle($title);
}
