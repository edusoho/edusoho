<?php

namespace Biz\OpenCourse\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\OpenCourse\Dao\RecommendedCourseDao;
use Biz\OpenCourse\Processor\CourseProcessorFactory;
use Biz\OpenCourse\Service\OpenCourseRecommendedService;

class OpenCourseRecommendedServiceImpl extends BaseService implements OpenCourseRecommendedService
{
    public function getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type)
    {
        return $this->getRecommendedCourseDao()->getByCourseIdAndType($openCourseId, $recommendCourseId, $type);
    }

    public function addRecommendedCourses($openCourseId, $recommendCourseIds, $type)
    {
        if (empty($recommendCourseIds)) {
            return true;
        }

        $recommendCourses = array();

        foreach ($recommendCourseIds as $key => $courseId) {
            $exitsRecommendCourse = $this->getRecommendedCourseByCourseIdAndType($openCourseId, $courseId, $type);

            if (!$exitsRecommendCourse) {
                $fields = array(
                    'recommendCourseId' => $courseId,
                    'openCourseId'      => $openCourseId,
                    'type'              => $type
                );
                $recommendCourses[] = $this->getRecommendedCourseDao()->create($fields);
            }
        }

        $recommendIds = ArrayToolkit::column($recommendCourses, 'id');

        $this->refreshCoursesSeq($openCourseId, $recommendIds);
        return $recommendCourses;
    }

    public function updateOpenCourseRecommendedCourses($openCourseId, $activeRecommendIds)
    {
        $allExistingRecommendedCourses = $this->findRecommendedCoursesByOpenCourseId($openCourseId);

        $existRecommendedIds = ArrayToolkit::column($allExistingRecommendedCourses, 'recommendCourseId');

        if (empty($activeRecommendIds)) {
            $this->deleteBatchRecommendCourses($existRecommendedIds);
        } else {
            $diff = array_diff($existRecommendedIds, $activeRecommendIds);

            if (!empty($diff)) {
                $this->deleteBatchRecommendCourses($diff);
            }
        }

        $this->refreshCoursesSeq($openCourseId, $activeRecommendIds);
    }

    public function findRecommendedCoursesByOpenCourseId($openCourseId)
    {
        return $this->getRecommendedCourseDao()->findByOpenCourseId($openCourseId);
    }

    protected function refreshCoursesSeq($openCourseId, $recommendIds)
    {
        $existingRecommended = $this->findRecommendedCoursesByOpenCourseId($openCourseId);
        $existingRecommended = ArrayToolkit::index($existingRecommended, 'recommendCourseId');

        $seq = 1;

        if (empty($recommendIds)) {
            return;
        }

        foreach ($recommendIds as $key => $recommendId) {
            $existing = empty($existingRecommended[$recommendId]) ? array() : $existingRecommended[$recommendId];

            if ($existing) {
                $this->getRecommendedCourseDao()->update($existing['id'], array('seq' => $seq));
                $seq++;
            }
        }

        return true;
    }

    public function deleteRecommendCourse($recommendId)
    {
        return $this->deleteBatchRecommendCourses(array($recommendId));
    }

    protected function deleteBatchRecommendCourses($recommendIds)
    {
        if (empty($recommendIds)) {
            return true;
        }

        foreach ($recommendIds as $key => $recommendId) {
            $this->getRecommendedCourseDao()->delete($recommendId);
        }

        return true;
    }

    protected function addRecommendeds($recommendCourseIds, $openCourseId, $type)
    {
        foreach ($recommendCourseIds as $key => $courseId) {
            $recommended = array(
                'recommendCourseId' => $courseId,
                'openCourseId'      => $openCourseId,
                'type'              => $type
            );
            $this->getRecommendedCourseDao()->create($recommended);
        }

        return true;
    }

    public function countRecommends($conditions)
    {
        return $this->getRecommendedCourseDao()->count($conditions);
    }

    public function searchRecommends($conditions, $orderBy, $start, $limit)
    {
        return $this->getRecommendedCourseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function recommendedCoursesSort($recommendCourses)
    {
        $courses = array();

        foreach ($recommendCourses as $key => $course) {
            $course = $this->getTypeCourseService($course['type'])->getCourse($course['recommendCourseId']);

            if ($course) {
                $courses[$course['id']] = $course;
            }
        }

        return $courses;
    }

    public function findRandomRecommendCourses($courseId, $num = 3)
    {
        if ($num < 0) {
            throw $this->createServiceException('num must be a unsigned int');
        }
        $recommendCourses = $this->getRecommendedCourseDao()->findRandomRecommendCourses($courseId, $num);

        $courseIds = ArrayToolkit::column($recommendCourses, 'recommendCourseId');

        return $this->getTypeCourseService('course')->findCoursesByIds($courseIds);
    }

    protected function getTypeCourseService($type)
    {
        return CourseProcessorFactory::create($type);
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return RecommendedCourseDao
     */
    protected function getRecommendedCourseDao()
    {
        return $this->createDao('OpenCourse:RecommendedCourseDao');
    }
}
