<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function createMultiClass($fields);

    public function getMultiClassByTitle($title);

    public function updateMultiClass($id, $fields);

    public function deleteMultiClass($id);
}
