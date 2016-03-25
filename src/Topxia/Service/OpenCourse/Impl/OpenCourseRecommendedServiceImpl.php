<?php

namespace Topxia\Service\OpenCourse\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\OpenCourse\OpenCourseRecommendedService;

class OpenCourseRecommendedServiceImpl extends BaseService implements OpenCourseRecommendedService
{
    public function addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds)
    {
        $allExistingRecommendedCourses = $this->findRecommendedCoursesByOpenCourseId($openCourseId);

        $existRecommendedCourseIds = array();

        foreach ($allExistingRecommendedCourses as $key => $existCourse) {
            $existRecommendedCourseIds[] = $existCourse['recommendCourseId'];
        }

        if (empty($existRecommendedCourseIds)) {
            $this->addRecommendeds($recommendCourseIds, $openCourseId);
        } else {
            $diff = array_values(array_diff($recommendCourseIds, $existRecommendedCourseIds));

            if (!empty($diff)) {
                $this->addRecommendeds($diff, $openCourseId);
            }
        }

        $this->refreshCoursesSeq($openCourseId, $recommendCourseIds);
    }

    public function updateOpenCourseRecommendedCourses($openCourseId, $activeCourseIds)
    {
        $this->getCourseService()->tryManageCourse($openCourseId);
        $allExistingRecommendedCourses = $this->findRecommendedCoursesByOpenCourseId($openCourseId);

        $existRecommendedCourseIds = array();

        foreach ($allExistingRecommendedCourses as $key => $existCourse) {
            $existRecommendedCourseIds[] = $existCourse['recommendCourseId'];
        }

        $diff = array_diff($existRecommendedCourseIds, $activeCourseIds);

        if (!empty($diff)) {
            foreach ($diff as $recommendedCourseId) {
                $this->deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendedCourseId);
            }
        }

        $this->refreshCoursesSeq($openCourseId, $activeCourseIds);
    }

    public function findRecommendedCoursesByOpenCourseId($openCourseId)
    {
        $recommendCourseIds = $this->getRecommendedCourseDao()->findRecommendedCoursesByOpenCourseId($openCourseId);
        return $recommendCourseIds;
    }

    public function findRecommendCourse($openCourseId, $recommendCourseId)
    {
        return $this->getRecommendedCourseDao()->findRecommendCourse($openCourseId, $recommendCourseId);
    }

    protected function refreshCoursesSeq($openCourseId, $recommendCourseIds)
    {
        $seq = 1;

        foreach ($recommendCourseIds as $key => $recommendCourseId) {
            $recommendCourse = $this->findRecommendCourse($openCourseId, $recommendCourseId);
            $this->getRecommendedCourseDao()->update($recommendCourse['id'], array('seq' => $seq));
            $seq++;
        }
    }

    protected function deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendCourseId)
    {
        $this->getRecommendedCourseDao()->deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendCourseId);
    }

    protected function addRecommendeds($recommendCourseIds, $openCourseId)
    {
        $counts = count($recommendCourseIds);

        for ($i = 0; $i < $counts; $i++) {
            $course      = $this->getCourseService()->getCourse($recommendCourseIds[$i]);
            $recommended = array(
                'recommendCourseId' => $recommendCourseIds[$i],
                'openCourseId'      => $openCourseId,
                'type'              => $course['type']
            );
            $this->getRecommendedCourseDao()->addRecommendedCourse($recommended);
        }
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getRecommendedCourseDao()
    {
        return $this->createDao('OpenCourse.RecommendedCourseDao');
    }
}
