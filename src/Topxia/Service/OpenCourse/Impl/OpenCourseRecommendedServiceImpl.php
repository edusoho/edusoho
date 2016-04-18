<?php

namespace Topxia\Service\OpenCourse\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\OpenCourse\OpenCourseRecommendedService;
use Topxia\Common\ArrayToolkit;

class OpenCourseRecommendedServiceImpl extends BaseService implements OpenCourseRecommendedService
{
    public function addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds, $origin)
    {
        $allExistingRecommendedCourses = $this->findRecommendedCoursesByOpenCourseId($openCourseId);

        $existRecommendedCourseIds = array();

        foreach ($allExistingRecommendedCourses as $key => $existCourse) {
            $existRecommendedCourseIds[] = $existCourse['recommendCourseId'];
        }

        if (empty($existRecommendedCourseIds)) {
            $this->addRecommendeds($recommendCourseIds, $openCourseId, $origin);
        } else {
            $diff = array_values(array_diff($recommendCourseIds, $existRecommendedCourseIds));

            if (!empty($diff)) {
                $this->addRecommendeds($diff, $openCourseId, $origin);
            }
        }

        $this->refreshCoursesSeq($openCourseId, $recommendCourseIds);
    }

    public function updateOpenCourseRecommendedCourses($openCourseId, $activeCourseIds)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($openCourseId);
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
        $recommendCourses = $this->getRecommendedCourseDao()->findRecommendedCoursesByOpenCourseId($openCourseId);
        return $recommendCourses;
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

    protected function addRecommendeds($recommendCourseIds, $openCourseId, $origin)
    {
        $counts = count($recommendCourseIds);

        for ($i = 0; $i < $counts; $i++) {
            $course      = $this->getOpenCourseService()->getCourse($recommendCourseIds[$i]);
            $recommended = array(
                'recommendCourseId' => $recommendCourseIds[$i],
                'openCourseId'      => $openCourseId,
                'type'              => $course['type'],
                'origin'            => $origin
            );
            $this->getRecommendedCourseDao()->addRecommendedCourse($recommended);
        }
    }

    public function searchRecommendCount($conditions)
    {
        return $this->getRecommendedCourseDao()->searchRecommendCount($conditions);
    }

    public function searchRecommends($conditions, $orderBy, $start, $limit)
    {
        return $this->getRecommendedCourseDao()->searchRecommends($conditions, $orderBy, $start, $limit);
    }

    public function recommendedCoursesSort($recommendCourses)
    {
        $courseIds = ArrayToolkit::column($recommendCourses,'recommendCourseId');
        $totallyCourses = $this->getOpenCourseService()->searchCourses(
            array('courseIds'=>$courseIds),
            array('createdTime','DESC'),
            0, PHP_INT_MAX
        );
        $totallyCourses = ArrayToolkit::index($totallyCourses,'recommendCourseId');

        $courses = array();
        foreach ($recommendCourses as $key => $value) {
            $courses[$val['recommendCourseId']] = $totallyCourses[$val['recommendCourseId']];
        }

        return $courses;
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse.OpenCourseService');
    }

    protected function getRecommendedCourseDao()
    {
        return $this->createDao('OpenCourse.RecommendedCourseDao');
    }
}
