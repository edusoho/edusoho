<?php

namespace Topxia\Service\OpenCourse\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
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

        $this->getLogService()->info('openCourse', 'update', "更新公开课课程《{$course['title']}》(#{$course['id']})的信息", $fields);

        $updatedCourse = $this->getOpenCourseDao()->updateCourse($id, $fields);

        $this->dispatchEvent("open.course.update", array('argument' => $argument, 'course' => $updatedCourse));

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

        $update_picture = $this->updateCourse($courseId, $fields);

        $this->dispatchEvent("open.course.picture.update", array('argument' => $data, 'course' => $update_picture));

        return $update_picture;
    }

    public function favoriteCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            throw $this->createAccessDeniedException();
        }

        $course = $this->getCourse($courseId);

/*if ($course['status'] != 'published') {
throw $this->createServiceException('不能收藏未发布课程');
}*/

        if (empty($course)) {
            throw $this->createServiceException("该课程不存在,收藏失败!");
        }

        $favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id'], 'openCourse');

        if ($favorite) {
            throw $this->createServiceException("该收藏已经存在，请不要重复收藏!");
        }

        //添加动态
        $this->dispatchEvent(
            'open.course.favorite',
            new ServiceEvent($course)
        );

        $this->getFavoriteDao()->addFavorite(array(
            'courseId'    => $course['id'],
            'userId'      => $user['id'],
            'createdTime' => time(),
            'type'        => 'openCourse'
        ));

        $courseFavoriteNum = $this->getFavoriteDao()->searchCourseFavoriteCount(array(
            'courseId' => $courseId,
            'type'     => 'openCourse'
        ));

        return $courseFavoriteNum;
    }

    public function unFavoriteCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            throw $this->createAccessDeniedException();
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("该课程不存在,收藏失败!");
        }

        $favorite = $this->getFavoriteDao()->getFavoriteByUserIdAndCourseId($user['id'], $course['id'], 'openCourse');

        if (empty($favorite)) {
            throw $this->createServiceException("你未收藏本课程，取消收藏失败!");
        }

        $this->getFavoriteDao()->deleteFavorite($favorite['id']);

        $courseFavoriteNum = $this->getFavoriteDao()->searchCourseFavoriteCount(array(
            'courseId' => $courseId,
            'type'     => 'openCourse'
        ));

        return $courseFavoriteNum;
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

    public function updateLesson($courseId, $lessonId, $fields)
    {
        return $this->getOpenCourseLessonDao()->updateLesson($lessonId, $fields);
    }

    public function deleteLesson($id)
    {
        return $this->getOpenCourseLessonDao()->deleteLesson($id);
    }

    public function generateLessonReplay($courseId, $lessonId)
    {
        $lesson = $this->getLesson($lessonId);

        $client     = new EdusohoLiveClient();
        $replayList = $client->createReplayList($lesson["mediaId"], "录播回放", $lesson["liveProvider"]);

        if (isset($replayList['error']) && !empty($replayList['error'])) {
            return $replayList;
        }

        $this->getCourseLessonReplayDao()->deleteLessonReplayByLessonId($lessonId, 'openCourse');

        if (isset($replayList['data']) && !empty($replayList['data'])) {
            $replayList = json_decode($replayList["data"], true);
        }

        foreach ($replayList as $key => $replay) {
            $fields                = array();
            $fields["courseId"]    = $courseId;
            $fields["lessonId"]    = $lessonId;
            $fields["title"]       = $replay["subject"];
            $fields["replayId"]    = $replay["id"];
            $fields["userId"]      = $this->getCurrentUser()->id;
            $fields["createdTime"] = time();
            $courseLessonReplay    = $this->getCourseLessonReplayDao()->addCourseLessonReplay($fields);
        }

        $fields = array(
            "replayStatus" => "generated"
        );

        $lesson = $this->updateLesson($courseId, $lessonId, $fields);

        $this->dispatchEvent("course.lesson.generate.replay", $courseReplay);

        return $replayList;
    }

    /**
     * open_course_member
     */
    public function getMember($id)
    {
        return $this->getOpenCourseMemberDao()->getMember($id);
    }

    public function getCourseMember($courseId, $userId)
    {
        return $this->getOpenCourseMemberDao()->getCourseMember($courseId, $userId);
    }

    public function getCourseMemberByIp($courseId, $ip)
    {
        return $this->getOpenCourseMemberDao()->getCourseMemberByIp($courseId, $ip);
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
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $member['userId'] = $user['id'];
        } else {
            $member['userId'] = 0;
        }

        $member['createdTime'] = time();

        $newMember = $this->getOpenCourseMemberDao()->addMember($member);

        $this->dispatchEvent("open.course.member.create", array('argument' => $member, 'newMember' => $newMember));

        return $newMember;
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
        } elseif ($lesson['type'] == 'live' || $lesson['type'] == 'liveOpen') {
        } else {
            $lesson['mediaId']     = 0;
            $lesson['mediaName']   = '';
            $lesson['mediaSource'] = '';
            $lesson['mediaUri']    = '';
        }

        unset($lesson['media']);

        return $lesson;
    }

    protected function _filterCourseFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title'      => '',
            'subtitle'   => '',
            'about'      => '',
            'categoryId' => 0,
            'tags'       => '',
            'startTime'  => 0,
            'endTime'    => 0,
            'locationId' => 0,
            'address'    => '',
            'locked'     => 0,
            'hitNum'     => 0,
            'likeNum'    => 0,
            'postNum'    => 0
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

    protected function getFavoriteDao()
    {
        return $this->createDao('Course.FavoriteDao');
    }

    protected function getCourseLessonReplayDao()
    {
        return $this->createDao('Course.CourseLessonReplayDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }
}
