<?php

namespace Biz\MultiClass\Service;

interface MultiClassService
{
    public function findAllMultiClass();

    public function findByProductIds(array $productIds);

    public function findMultiClassesByCourseIds($courseIds);

    public function findByProductId($productId);

    public function findMultiClassesByCreator($creator);

    public function findMultiClassesByReplayShow($isReplayShow);

    public function getMultiClass($id);

    public function createMultiClass($fields);

    public function getMultiClassByTitle($title);

    public function updateMultiClass($id, $fields);

    public function deleteMultiClass($id);

    public function searchMultiClassJoinCourse($conditions, $orderBys, $start, $limit);

    public function searchUserTeachMultiClass($userId, $conditions, $start, $limit);

    public function countUserTeachMultiClass($userId, $conditions);

    public function searchMultiClass($conditions, $orderBys, $start, $limit, $columns = []);

    public function countMultiClass($conditions);

    public function cloneMultiClass($id, $cloneMultiClass);

    public function countMultiClassByCopyId($id);

    public function getMultiClassByCourseId($courseId);

    public function generateMultiClassTimeRange($courseId);

    public function updateMultiClassBundleNo($id, $bundleNo);
}
