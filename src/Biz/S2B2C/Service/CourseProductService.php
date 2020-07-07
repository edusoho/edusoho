<?php

namespace Biz\S2B2C\Service;

interface CourseProductService
{
    public function syncCourses($s2b2cProductId);

    public function updateProductVersionData($remoteProductId);

    public function deleteProductsByCourseSet($courseSet);

    public function closeProducts($products);

    public function checkCourseStatus($localCourseId);

    public function checkCourseSetStatus($localCourseSetId);

    /**
     * 通知
     *
     * @param $s2b2cProductId
     * @param $remoteResourceId
     * @param $lessonId
     *
     * @return mixed
     */
    public function closeTask($s2b2cProductId, $remoteResourceId, $lessonId);

    /**
     * 通知
     *
     * @param $remoteResourceId
     * @param $remoteCourseId
     * @param $priceFields
     *
     * @return mixed
     */
    public function syncProductPrice($remoteResourceId, $remoteCourseId, $priceFields);

    /**
     * 通知
     *
     * @param $s2b2cProductId
     * @param $remoteResourceId
     *
     * @return mixed
     */
    public function closeCourse($s2b2cProductId, $remoteResourceId);

    /**
     * 通知
     *
     * @param $s2b2cProductId
     * @param $remoteCourseSetId
     *
     * @return mixed
     */
    public function closeCourseSet($s2b2cProductId, $remoteCourseSetId);
}
