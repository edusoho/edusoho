<?php

namespace Biz\S2B2C\Service;

interface CourseProductService
{
    public function syncCourses($s2b2cProductId);

    public function updateCourseVersionData($courseId);

    public function updateProductVersionData($remoteProductId);

    public function setProductHasNewVersion($productType, $remoteResourceId);

    public function deleteProductsByCourseSet($courseSet);

    public function closeProducts($products);

    public function checkCourseStatus($localCourseId);

    public function checkCourseSetStatus($localCourseSetId);

    public function syncProductPrice($remoteResourceId, $priceFields);

    public function closeTask($remoteResourceId, $lessonId);
}
