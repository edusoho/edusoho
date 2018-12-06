<?php

namespace Biz\OpenCourse\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\OpenCourse\Dao\OpenCourseLessonDao;
use Biz\OpenCourse\Dao\OpenCourseMemberDao;
use Biz\OpenCourse\Dao\RecommendedCourseDao;
use Biz\OpenCourse\Service\OpenCourseDeleteService;

class OpenCourseDeleteServiceImpl extends BaseService implements OpenCourseDeleteService
{
    public function delete($courseId, $type)
    {
        try {
            $this->beginTransaction();
            $types = array('lessons', 'members', 'course', 'recommend', 'materials');

            if (!in_array($type, $types)) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $method = 'delete'.ucwords($type);
            $course = $this->getOpenCourseService()->getCourse($courseId);
            $result = $this->$method($course);
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function deleteLessons($course)
    {
        $lessonCount = $this->getOpenCourseLessonDao()->count(array('courseId' => $course['id']));

        $count = 0;

        if ($lessonCount > 0) {
            $lessons = $this->getOpenCourseLessonDao()->search(array('courseId' => $course['id']), array('createdTime' => 'desc'), 0, 500);

            foreach ($lessons as $lesson) {
                if (!empty($lesson['mediaId'])) {
                    $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
                }

                $result = $this->getOpenCourseLessonDao()->delete($lesson['id']);

                $this->dispatchEvent('open.course.lesson.delete', array('lesson' => $lesson));

                $count += $result;
            }

            $lessonLog = "删除公开课《{$course['title']}》(#{$course['id']})的所有课时";
            $this->getLogService()->info('open_course', 'delete_lesson', $lessonLog);
        }

        return $count;
    }

    protected function deleteMembers($course)
    {
        $memberCount = $this->getOpenCourseMemberDao()->count(array('courseId' => $course['id']));
        $count = 0;

        if ($memberCount > 0) {
            $members = $this->getOpenCourseMemberDao()->search(array('courseId' => $course['id']), array('createdTime' => 'desc'), 0, 500);

            foreach ($members as $member) {
                $result = $this->getOpenCourseMemberDao()->delete($member['id']);
                $count += $result;
            }

            $memberLog = "删除公开课《{$course['title']}》(#{$course['id']})的成员";
            $this->getLogService()->info('open_course', 'delete_member', $memberLog);
        }

        return $count;
    }

    protected function deleteRecommend($course)
    {
        $openCount = $this->getRecommendCourseDao()->count(array('openCourseId' => $course['id']));
        $count = 0;

        if ($openCount > 0) {
            $openCourses = $this->getRecommendCourseDao()->search(array('openCourseId' => $course['id']), array('createdTime' => 'desc'), 0, 500);

            foreach ($openCourses as $openCourse) {
                $result = $this->getRecommendCourseDao()->delete($openCourse['id']);
                $count += $result;
            }

            $memberLog = "删除公开课《{$course['title']}》(#{$course['id']})的所有推荐课程";
            $this->getLogService()->info('open_course', 'delete_recommend_course', $memberLog);
        }

        return $count;
    }

    protected function deleteCourse($course)
    {
        $this->getOpenCourseDao()->delete($course['id']);
        $courseLog = "删除公开课《{$course['title']}》(#{$course['id']})";
        $this->getLogService()->info('open_course', 'delete_course', $courseLog);

        return 0;
    }

    protected function deleteMaterials($course)
    {
        $conditions = array('courseId' => $course['id'], 'type' => 'openCourse');
        $materialCount = $this->getMaterialService()->countMaterials($conditions);
        $count = 0;

        if ($materialCount > 0) {
            $materials = $this->getMaterialService()->searchMaterials($conditions, array('createdTime' => 'DESC'), 0, $materialCount);

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
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    protected function getOpenCourseDao()
    {
        return $this->createDao('OpenCourse:OpenCourseDao');
    }

    /**
     * @return OpenCourseLessonDao
     */
    protected function getOpenCourseLessonDao()
    {
        return $this->createDao('OpenCourse:OpenCourseLessonDao');
    }

    /**
     * @return OpenCourseMemberDao
     */
    protected function getOpenCourseMemberDao()
    {
        return $this->createDao('OpenCourse:OpenCourseMemberDao');
    }

    /**
     * @return RecommendedCourseDao
     */
    protected function getRecommendCourseDao()
    {
        return $this->createDao('OpenCourse:RecommendedCourseDao');
    }
}
