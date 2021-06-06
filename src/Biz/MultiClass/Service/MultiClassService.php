<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function findAllMultiClass();

    public function findByProductIds(array $productIds);

    public function findMultiClassesByCourseIds($courseIds);

    public function findByProductId($productId);

    public function getMultiClass($id);

    public function createMultiClass($fields);

    public function getMultiClassByTitle($title);

    public function updateMultiClass($id, $fields);

    public function deleteMultiClass($id);

    public function cloneMultiClass($id, $cloneMultiClass);

    public function countMultiClassByCopyId($id);

    public function getMultiClassByCourseId($courseId);
}
