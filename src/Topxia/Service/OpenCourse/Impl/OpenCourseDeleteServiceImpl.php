<?php

namespace Topxia\Service\OpenCourse\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\OpenCourse\OpenCourseDeleteService;

class OpenCourseDeleteServiceImpl extends BaseService implements OpenCourseDeleteService
{
    public function delete($courseId, $type)
    {
        try {
            $this->getOpenCourseDao()->getConnection()->beginTransaction();
            $course = $this->getOpenCourseService()->getCourse($courseId);

            $types = array('lessons', 'members', 'course', 'recommend', 'materials');

            if (!in_array($type, $types)) {
                throw $this->createServiceException('未知类型,删除失败');
            }

            $method = 'delete'.ucwords($type);
            $result = $this->$method($course);
            $this->getOpenCourseDao()->getConnection()->commit();

            return $result;
        } catch (\Exception $e) {
            $this->getOpenCourseDao()->getConnection()->rollback();
            throw $e;
        }
    }

    protected function deleteLessons($course)
    {
        $lessonCount = $this->getOpenCourseLessonDao()->searchLessonCount(array('courseId' => $course['id']));

        $count = 0;

        if ($lessonCount > 0) {
            $lessons = $this->getOpenCourseLessonDao()->searchLessons(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($lessons as $lesson) {
                if (!empty($lesson['mediaId'])) {
                    $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
                }

                $this->getCrontabService()->deleteJobs($lesson['id'], 'liveOpenLesson');

                $result = $this->getOpenCourseLessonDao()->deleteLesson($lesson['id']);
                $count += $result;
            }

            $lessonLog = "删除公开课《{$course['title']}》(#{$course['id']})的所有课时";
            $this->getLogService()->info('open_course', 'delete_lesson', $lessonLog);
        }

        return $count;
    }

    protected function deleteMembers($course)
    {
        $memberCount = $this->getOpenCourseMemberDao()->searchMemberCount(array('courseId' => $course['id']));
        $count       = 0;

        if ($memberCount > 0) {
            $members = $this->getOpenCourseMemberDao()->searchMembers(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($members as $member) {
                $result = $this->getOpenCourseMemberDao()->deleteMember($member['id']);
                $count += $result;
            }

            $memberLog = "删除公开课《{$course['title']}》(#{$course['id']})的成员";
            $this->getLogService()->info('open_course', 'delete_member', $memberLog);
        }

        return $count;
    }

    protected function deleteRecommend($course)
    {
        $openCount = $this->getRecommendCourseDao()->searchRecommendCount(array('openCourseId' => $course['id']));
        $count     = 0;

        if ($openCount > 0) {
            $openCourses = $this->getRecommendCourseDao()->searchRecommends(array('openCourseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($openCourses as $openCourse) {
                $result = $this->getRecommendCourseDao()->deleteRecommendedCourse($openCourse['id']);
                $count += $result;
            }

            $memberLog = "删除公开课《{$course['title']}》(#{$course['id']})的所有推荐课程";
            $this->getLogService()->info('open_course', 'delete_recommend_course', $memberLog);
        }

        return $count;
    }

    protected function deleteCourse($course)
    {
        $this->getOpenCourseDao()->deleteCourse($course['id']);
        $courseLog = "删除公开课《{$course['title']}》(#{$course['id']})";
        $this->getLogService()->info('open_course', 'delete_course', $courseLog);
        return 0;
    }

    protected function deleteMaterials($course)
    {
        $conditions    = array('courseId' => $course['id'], 'type' => 'openCourse');
        $materialCount = $this->getMaterialService()->searchMaterialCount($conditions);
        $count         = 0;

        if ($materialCount > 0) {
            $materials = $this->getMaterialService()->searchMaterials($conditions, array('createdTime', 'DESC'), 0, $materialCount);

            foreach ($materials as $material) {
                $result = $this->getMaterialService()->deleteMaterial($course['id'], $material['id']);
                $count += $result;
            }

            $materialLog = "删除公开课《{$course['title']}》(#{$course['id']})的所有课时资料";
            $this->getLogService()->info('open_course', 'delete_material', $materialLog);
        }

        return $count;
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse.OpenCourseService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getMaterialService()
    {
        return $this->createService('Course.MaterialService');
    }

    protected function getOpenCourseDao()
    {
        return $this->createDao('OpenCourse.OpenCourseDao');
    }

    protected function getOpenCourseLessonDao()
    {
        return $this->createDao('OpenCourse.OpenCourseLessonDao');
    }

    protected function getOpenCourseMemberDao()
    {
        return $this->createDao('OpenCourse.OpenCourseMemberDao');
    }

    protected function getRecommendCourseDao()
    {
        return $this->createDao('OpenCourse.RecommendedCourseDao');
    }

    protected function getCrontabService()
    {
        return $this->createService('Crontab.CrontabService');
    }
}
