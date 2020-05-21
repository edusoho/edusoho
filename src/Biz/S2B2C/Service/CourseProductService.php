<?php

namespace Biz\S2B2C\Service;

interface CourseProductService
{
    public function syncCourses($localCourseSet, $product);

    public function syncCourseMain($courseId);

    public function setProductHasNewVersion($productType, $remoteResourceId);

    public function deleteProductsByCourseSet($courseSet);

    public function closeProducts($products);

    public function checkCourseStatus($localCourseId);

    public function checkCourseSetStatus($localCourseSetId);

    public function syncProductPrice($remoteResourceId, $priceFields);
}
