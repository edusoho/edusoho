<?php

namespace Topxia\Service\OpenCourse\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\OpenCourse\OpenCourseService;

class OpenCourseServiceImpl extends BaseService implements OpenCourseService
{
    /**
     * open_course
     */
    public function getCourse($id)
    {
        return $this->getOpenCourseDao()->getCourse($id);
    }

    public function findCoursesByIds(array $ids)
    {
        return $this->getOpenCourseDao()->findCoursesByIds($ids);
    }

    public function findCoursesByParentIdAndLocked($parentId, $locked)
    {
        return $this->getOpenCourseDao()->findCoursesByParentIdAndLocked($parentId, $locked);
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        return $this->getOpenCourseDao()->searchCourses($conditions, $orderBy, $start, $limit);
    }

    public function searchCourseCount($conditions)
    {
        return $this->getOpenCourseDao()->searchCourseCount($conditions);
    }

    public function createCourse($course)
    {
        if (!ArrayToolkit::requireds($course, array('title'))) {
            throw $this->createServiceException('缺少必要字段，创建课程失败！');
        }

        $course                = ArrayToolkit::parts($course, array('title', 'type', 'about', 'categoryId', 'tags'));
        $course['status']      = 'draft';
        $course['about']       = !empty($course['about']) ? $this->purifyHtml($course['about']) : '';
        $course['tags']        = !empty($course['tags']) ? $course['tags'] : '';
        $course['userId']      = $this->getCurrentUser()->id;
        $course['createdTime'] = time();
        $course['teacherIds']  = array($course['userId']);
        $course                = $this->getOpenCourseDao()->addCourse($course);

        $member = array(
            'courseId'    => $course['id'],
            'userId'      => $course['userId'],
            'role'        => 'teacher',
            'createdTime' => time()
        );

        $this->getOpenCourseMemberDao()->addMember($member);

        $this->getLogService()->info('openCourse', 'create', "创建公开课《{$course['title']}》(#{$course['id']})");

        return $course;
    }

    public function updateCourse($id, $fields)
    {
        $argument = $fields;
        $course   = $this->getOpenCourseDao()->getCourse($id);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，更新失败！');
        }

        $fields = $this->_filterCourseFields($fields);

        return $this->getOpenCourseDao()->updateCourse($id, $fields);
    }

    public function deleteCourse($id)
    {
        return $this->getOpenCourseDao()->deleteCourse($id);
    }

    public function waveCourse($id, $field, $diff)
    {
        return $this->getOpenCourseDao()->waveCourse($id, $field, $diff);
    }

    public function publishCourse($id)
    {
        $course = $this->tryManageOpenCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $this->getOpenCourseDao()->updateCourse($id, array('status' => 'published'));
    }

    public function closeCourse($id)
    {
        $course = $this->tryManageOpenCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $this->getOpenCourseDao()->updateCourse($id, array('status' => 'closed'));
    }

    public function tryManageOpenCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        $course = $this->getOpenCourseDao()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        if (!$this->hasOpenCourseManagerRole($courseId, $user['id'])) {
            throw $this->createAccessDeniedException('您不是课程的教师或管理员，无权操作！');
        }

        return $course;
    }

    public function changeCoursePicture($courseId, $data)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，图标更新失败！');
        }

        $fileIds = ArrayToolkit::column($data, "id");
        $files   = $this->getFileService()->getFilesByIds($fileIds);

        $files   = ArrayToolkit::index($files, "id");
        $fileIds = ArrayToolkit::index($data, "type");

        $fields = array(
            'smallPicture'  => $files[$fileIds["small"]["id"]]["uri"],
            'middlePicture' => $files[$fileIds["middle"]["id"]]["uri"],
            'largePicture'  => $files[$fileIds["large"]["id"]]["uri"]
        );

        $this->_deleteNotUsedPictures($course);

        $this->getLogService()->info('open_course', 'update_picture', "更新公开课《{$course['title']}》(#{$course['id']})图片", $fields);

        $update_picture = $this->getOpenCourseDao()->updateCourse($courseId, $fields);

        $this->dispatchEvent("open.course.picture.update", array('argument' => $data, 'course' => $update_picture));

        return $update_picture;
    }

    /**
     * open_course_lesson
     */
    public function getLesson($id)
    {
        return $this->getOpenCourseLessonDao()->getLesson($id);
    }

    public function findLessonsByIds(array $ids)
    {
        return $this->getOpenCourseLessonDao()->findLessonsByIds($ids);
    }

    public function findLessonsByCourseId($courseId)
    {
        return $this->getOpenCourseLessonDao()->findLessonsByCourseId($courseId);
    }

    public function searchLessons($condition, $orderBy, $start, $limit)
    {
        return $this->getOpenCourseLessonDao()->searchLessons($condition, $orderBy, $start, $limit);
    }

    public function searchLessonCount($conditions)
    {
        return $this->getOpenCourseLessonDao()->searchLessonCount($conditions);
    }

    public function createLesson($lesson)
    {
        $lesson = ArrayToolkit::filter($lesson, array(
            'courseId'      => 0,
            'chapterId'     => 0,
            'seq'           => 0,
            'free'          => 0,
            'title'         => '',
            'summary'       => '',
            'tags'          => array(),
            'type'          => 'text',
            'content'       => '',
            'media'         => array(),
            'mediaId'       => 0,
            'length'        => 0,
            'startTime'     => 0,
            'giveCredit'    => 0,
            'requireCredit' => 0,
            'liveProvider'  => 'none',
            'copyId'        => 0,
            'testMode'      => 'normal',
            'testStartTime' => 0,
            'suggestHours'  => '0.0',
            'copyId'        => 0
        ));

        if (!ArrayToolkit::requireds($lesson, array('courseId', 'title', 'type'))) {
            throw $this->createServiceException('参数缺失，创建课时失败！');
        }

        if (empty($lesson['courseId'])) {
            throw $this->createServiceException('添加课时失败，课程ID为空。');
        }

        $course = $this->getCourse($lesson['courseId'], true);

        if (empty($course)) {
            throw $this->createServiceException('添加课时失败，课程不存在。');
        }

        if (!in_array($lesson['type'], array('text', 'audio', 'video', 'liveOpen', 'open', 'ppt', 'document', 'flash'))) {
            throw $this->createServiceException('课时类型不正确，添加失败！');
        }

        $this->fillLessonMediaFields($lesson);

        if (isset($fields['title'])) {
            $fields['title'] = $this->purifyHtml($fields['title']);
        }

        // 课程处于发布状态时，新增课时，课时默认的状态为“未发布"
        $lesson['status']      = $course['status'] == 'published' ? 'unpublished' : 'published';
        $lesson['free']        = empty($lesson['free']) ? 0 : 1;
        $lesson['number']      = $this->_getNextLessonNumber($lesson['courseId']);
        $lesson['userId']      = $this->getCurrentUser()->id;
        $lesson['createdTime'] = time();

        if ($lesson['type'] == 'liveOpen') {
            $lesson['endTime']      = $lesson['startTime'] + $lesson['length'] * 60;
            $lesson['suggestHours'] = $lesson['length'] / 60;
        }

        $lesson = $this->getOpenCourseLessonDao()->addLesson($lesson);

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', 1);
        }

        $this->updateCourse($course['id'], array('lessonNum' => ($lesson['number'] - 1)));

        $this->getLogService()->info('openCourse', 'add_lesson', "添加公开课时《{$lesson['title']}》({$lesson['id']})", $lesson);
        $this->dispatchEvent("open.course.lesson.create", array('lesson' => $lesson));

        return $lesson;
    }

    public function updateLesson($id, $fields)
    {
        return $this->getOpenCourseLessonDao()->updateLesson($id, $fields);
    }

    public function deleteLesson($id)
    {
        return $this->getOpenCourseLessonDao()->deleteLesson($id);
    }

    /**
     * open_course_member
     */
    public function getMember($id)
    {
        return $this->getOpenCourseMemberDao()->getMember($id);
    }

    public function getMemberByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getOpenCourseMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
    }

    public function findMembersByCourseIds($courseIds)
    {
        return $this->getOpenCourseMemberDao()->findMembersByCourseIds($courseIds);
    }

    public function searchMemberCount($conditions)
    {
        return $this->getOpenCourseMemberDao()->searchMemberCount($conditions);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        return $this->getOpenCourseMemberDao()->searchMembers($conditions, $orderBy, $start, $limit);
    }

    public function createMember($member)
    {
        return $this->getOpenCourseMemberDao()->addMember($member);
    }

    public function updateMember($id, $member)
    {
        return $this->getOpenCourseMemberDao()->updateMember($id, $member);
    }

    public function deleteMember($id)
    {
        return $this->getOpenCourseMemberDao()->deleteMember($id);
    }

    protected function fillLessonMediaFields(&$lesson)
    {
        if (in_array($lesson['type'], array('video', 'audio', 'ppt', 'document', 'flash'))) {
            $media = empty($lesson['media']) ? null : $lesson['media'];

            if (empty($media) || empty($media['source']) || empty($media['name'])) {
                throw $this->createServiceException("media参数不正确，添加课时失败！");
            }

            if ($media['source'] == 'self') {
                $media['id'] = intval($media['id']);

                if (empty($media['id'])) {
                    throw $this->createServiceException("media id参数不正确，添加/编辑课时失败！");
                }

                $file = $this->getUploadFileService()->getFile($media['id']);

                if (empty($file)) {
                    throw $this->createServiceException('文件不存在，添加/编辑课时失败！');
                }

                $lesson['mediaId']     = $file['id'];
                $lesson['mediaName']   = $file['filename'];
                $lesson['mediaSource'] = 'self';
                $lesson['mediaUri']    = '';
            } else {
                if (empty($media['uri'])) {
                    throw $this->createServiceException("media uri参数不正确，添加/编辑课时失败！");
                }

                $lesson['mediaId']     = 0;
                $lesson['mediaName']   = $media['name'];
                $lesson['mediaSource'] = $media['source'];
                $lesson['mediaUri']    = $media['uri'];
            }
        } elseif ($lesson['type'] == 'testpaper') {
            $lesson['mediaId'] = $lesson['mediaId'];
        } elseif ($lesson['type'] == 'live') {
        } else {
            $lesson['mediaId']     = 0;
            $lesson['mediaName']   = '';
            $lesson['mediaSource'] = '';
            $lesson['mediaUri']    = '';
        }

        unset($lesson['media']);

        return $lesson;
    }

    protected function hasOpenCourseManagerRole($courseId, $userId)
    {
        if ($this->getUserService()->hasAdminRoles($userId)) {
            return true;
        }

        $member = $this->getOpenCourseMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if ($member && ($member['role'] == 'teacher')) {
            return true;
        }

        return false;
    }

    private function _getNextLessonNumber($courseId)
    {
        $lessonCount = $this->searchLessonCount(array('courseId' => $courseId));
        return ($lessonCount + 1);
    }

    private function _deleteNotUsedPictures($course)
    {
        $oldPictures = array(
            'smallPicture'  => $course['smallPicture'] ? $course['smallPicture'] : null,
            'middlePicture' => $course['middlePicture'] ? $course['middlePicture'] : null,
            'largePicture'  => $course['largePicture'] ? $course['largePicture'] : null
        );

        $courseCount = $this->searchCourseCount(array('smallPicture' => $course['smallPicture']));

        if ($courseCount <= 1) {
            $fileService = $this->getFileService();
            array_map(function ($oldPicture) use ($fileService) {
                if (!empty($oldPicture)) {
                    $fileService->deleteFileByUri($oldPicture);
                }
            }, $oldPictures);
        }
    }

    protected function _filterCourseFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title'      => '',
            'subtitle'   => '',
            'about'      => '',
            'categoryId' => 0,
            'tags'       => '',
            'studentNum' => 0,
            'locked'     => 0
        ));

        if (!empty($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'], true);
        }

        if (!empty($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
            array_walk($fields['tags'], function (&$item, $key) {
                $item = (int) $item['id'];
            }

            );
        }

        return $fields;
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
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

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }
}
