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
            $types  = array('lessons', 'members', 'course', 'recommend');

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
        $count       = 0;

        if ($lessonCount > 0) {
            $lessons = $this->getLessonDao()->searchLessons(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($lessons as $lesson) {
                if (!empty($lesson['mediaId'])) {
                    $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
                }

                $result = $this->getOpenCourseLessonDao()->deleteLesson($lesson['id']);
                $count += $result;
            }

            //删除定时任务

            $lessonLog = "删除课程《{$course['title']}》(#{$course['id']})的课时";
            $this->getLogService()->info('open.course.lesson', 'delete', $lessonLog);
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
                $result = $this->getOpensCourseMemberDao()->deleteMember($member['id']);
                $count += $result;
            }

            $memberLog = "删除课程《{$course['title']}》(#{$course['id']})的成员";
            $this->getLogService()->info('open.course.member', 'delete', $memberLog);
        }

        return $count;
    }

    protected function deleteCourse($course)
    {
        $this->getOpenCourseDao()->deleteCourse($course['id']);
        $courseLog = "删除课程《{$course['title']}》(#{$course['id']})";
        $this->getLogService()->info('open.course', 'delete', $courseLog);
        return 0;
    }

    protected function getCourseService()
    {
        return $this->createService('OpenCourse.OpenCourseService');
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
}
