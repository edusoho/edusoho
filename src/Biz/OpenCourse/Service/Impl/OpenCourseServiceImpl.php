<?php

namespace Biz\OpenCourse\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Dao\FavoriteDao;
use AppBundle\Common\ArrayToolkit;
use Biz\File\UploadFileException;
use Biz\OpenCourse\OpenCourseException;
use Biz\System\Service\LogService;
use Biz\OpenCourse\Dao\OpenCourseDao;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Biz\OpenCourse\Dao\OpenCourseLessonDao;
use Biz\OpenCourse\Dao\OpenCourseMemberDao;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\AppLoggerConstant;
use Biz\Util\EdusohoLiveClient;

class OpenCourseServiceImpl extends BaseService implements OpenCourseService
{
    /**
     * open_course.
     */
    public function getCourse($id)
    {
        return $this->getOpenCourseDao()->get($id);
    }

    public function findCoursesByIds(array $ids)
    {
        return $this->getOpenCourseDao()->findByIds($ids);
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getOpenCourseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countCourses($conditions)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getOpenCourseDao()->count($conditions);
    }

    public function createCourse($course)
    {
        $this->tryCreateCourse();

        if (!ArrayToolkit::requireds($course, array('title'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $course = ArrayToolkit::parts($course, array('title', 'type', 'about', 'categoryId', 'orgCode'));
        $course['status'] = 'draft';
        $course['about'] = !empty($course['about']) ? $this->purifyHtml($course['about']) : '';
        $course['userId'] = $this->getCurrentUser()->id;
        $course['teacherIds'] = array($course['userId']);
        $course = $this->fillOrgId($course);

        $course = $this->getOpenCourseDao()->create($course);

        $member = array(
            'courseId' => $course['id'],
            'userId' => $course['userId'],
            'role' => 'teacher',
        );

        $this->getOpenCourseMemberDao()->create($member);

        return $course;
    }

    protected function tryCreateCourse()
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin() || !($user->isTeacher() || $user->isAdmin() || $user->isSuperAdmin())) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }
    }

    protected function filterOpenCourseFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'subtitle',
            'status',
            'lessonNum',
            'categoryId',
            'tags',
            'smallPicture',
            'middlePicture',
            'largePicture',
            'about',
            'teacherIds',
            'studentNum',
            'hitNum',
            'likeNum',
            'postNum',
            'userId',
            'parentId',
            'locked',
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'createdTime',
            'updateTime',
            'orgId',
            'orgCode',
            'startTime',
            'length',
            'authUrl',
            'jumpUrl',
        ));

        return $fields;
    }

    protected function updateOpenCourse($course, $fields)
    {
        $user = $this->getCurrentUser();

        $courseFields = ArrayToolkit::parts($fields, array(
            'title',
            'subtitle',
            'status',
            'lessonNum',
            'categoryId',
            'tags',
            'smallPicture',
            'middlePicture',
            'largePicture',
            'about',
            'teacherIds',
            'studentNum',
            'hitNum',
            'likeNum',
            'postNum',
            'userId',
            'parentId',
            'locked',
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'createdTime',
            'updateTime',
            'orgId',
            'orgCode',
        ));

        $courseFields = $this->_filterCourseFields($courseFields);

        $tagIds = isset($courseFields['tags']) ? $courseFields['tags'] : null;

        unset($courseFields['tags']);

        $updatedCourse = $this->getOpenCourseDao()->update($course['id'], $courseFields);

        $this->dispatchEvent('open.course.update', array('argument' => $fields, 'course' => $updatedCourse, 'tagIds' => $tagIds, 'userId' => $user['id']));

        return $updatedCourse;
    }

    protected function shouldUpdateLiveLesson($course, $fields)
    {
        return 'liveOpen' == $course['type'] && isset($fields['startTime']) && !empty($fields['startTime']);
    }

    protected function updateLiveLesson($course, $fields)
    {
        $openLiveLesson = $this->searchLessons(
            array('courseId' => $course['id']),
            array('startTime' => 'DESC'),
            0,
            1
        );
        $liveLesson = $openLiveLesson ? $openLiveLesson[0] : array();

        $liveLessonFields = ArrayToolkit::parts($fields, array(
            'startTime',
            'length',
            'authUrl',
            'jumpUrl',
        ));

        $liveLessonFields = array_merge($liveLesson, $liveLessonFields);

        $liveLessonFields['type'] = 'liveOpen';
        $liveLessonFields['courseId'] = $course['id'];
        $liveLessonFields['title'] = $course['title'];

        $routes = array(
            'authUrl' => $fields['authUrl'],
            'jumpUrl' => $fields['jumpUrl'],
        );
        if ($openLiveLesson) {
            $this->getLiveCourseService()->editLiveRoom($course, $liveLessonFields, $routes);
            $this->updateLesson(
                $liveLessonFields['courseId'],
                $liveLessonFields['id'],
                $liveLessonFields
            );
        } else {
            $live = $this->getLiveCourseService()->createLiveRoom($course, $liveLessonFields, $routes);

            $liveLessonFields['mediaId'] = $live['id'];
            $liveLessonFields['liveProvider'] = $live['provider'];

            $this->createLesson($liveLessonFields);
        }
    }

    public function updateCourse($id, $fields)
    {
        $fields = $this->filterOpenCourseFields($fields);

        $course = $this->getCourse($id);
        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $updatedCourse = $this->updateOpenCourse($course, $fields);

        if ($this->shouldUpdateLiveLesson($course, $fields)) {
            $this->updateLiveLesson($course, $fields);
        }

        return $updatedCourse;
    }

    public function deleteCourse($id)
    {
        $course = $this->tryAdminCourse($id);

        $this->getOpenCourseMemberDao()->deleteByCourseId($id);
        $this->deleteLessonsByCourseId($id);

        $this->getOpenCourseDao()->delete($id);

        if ('liveOpen' == $course['type']) {
            $this->getLiveReplayService()->deleteReplaysByCourseId($id, 'liveOpen');
        }

        $this->dispatchEvent('open.course.delete', $course);

        return true;
    }

    public function waveCourse($id, $field, $diff)
    {
        $this->getOpenCourseDao()->wave(array($id), array($field => $diff));

        return $this->getCourse($id);
    }

    public function publishCourse($id)
    {
        $course = $this->tryManageOpenCourse($id);

        $lessonCount = $this->countLessons(array('courseId' => $id, 'status' => 'published'));

        if ($lessonCount < 1) {
            return array('result' => false, 'message' => '请先添加课时并发布！');
        }

        $course = $this->updateCourse($id, array('status' => 'published'));
        $this->dispatchEvent('open.course.publish', $course);

        return array('result' => true, 'course' => $course);
    }

    public function closeCourse($id)
    {
        $course = $this->tryManageOpenCourse($id);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $this->dispatchEvent('open.course.close', $course);

        return $this->getOpenCourseDao()->update($id, array('status' => 'closed'));
    }

    public function batchUpdateOrg($openCourseIds, $orgCode)
    {
        if (!is_array($openCourseIds)) {
            $openCourseIds = array($openCourseIds);
        }

        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

        foreach ($openCourseIds as $openCourseId) {
            $this->getOpenCourseDao()->update($openCourseId, $fields);
        }
    }

    public function tryManageOpenCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        if (!$this->hasOpenCourseManagerRole($courseId, $user['id'])) {
            $this->createNewException(OpenCourseException::FORBIDDEN_MANAGE_COURSE());
        }

        return $course;
    }

    public function tryAdminCourse($courseId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if (0 == count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN')))) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        return $course;
    }

    public function changeCoursePicture($courseId, $data)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $fileIds = ArrayToolkit::column($data, 'id');
        $files = $this->getFileService()->getFilesByIds($fileIds);

        $files = ArrayToolkit::index($files, 'id');
        $fileIds = ArrayToolkit::index($data, 'type');

        $fields = array(
            'smallPicture' => $files[$fileIds['small']['id']]['uri'],
            'middlePicture' => $files[$fileIds['middle']['id']]['uri'],
            'largePicture' => $files[$fileIds['large']['id']]['uri'],
        );

        $this->_deleteNotUsedPictures($course);

        $update_picture = $this->getOpenCourseDao()->update($courseId, $fields);

        $this->dispatchEvent('open.course.picture.update', array('argument' => $data, 'course' => $update_picture));

        return $update_picture;
    }

    public function favoriteCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        if ('published' != $course['status']) {
            $this->createNewException(OpenCourseException::FAVOR_UNPUBLISHED());
        }

        $favorite = $this->getFavoriteDao()->getByUserIdAndCourseId($user['id'], $course['id'], 'openCourse');

        if ($favorite) {
            $this->createNewException(OpenCourseException::DUPLICATE_FAVOR());
        }

        //添加动态
        $this->dispatchEvent(
            'open.course.favorite',
            new Event($course)
        );

        $this->getFavoriteDao()->create(array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'createdTime' => time(),
            'type' => 'openCourse',
        ));

        $courseFavoriteNum = $this->getFavoriteDao()->count(array(
            'courseId' => $courseId,
            'type' => 'openCourse',
        ));

        return $courseFavoriteNum;
    }

    public function unFavoriteCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (empty($user['id'])) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $favorite = $this->getFavoriteDao()->getByUserIdAndCourseId($user['id'], $course['id'], 'openCourse');

        if (empty($favorite)) {
            $this->createNewException(OpenCourseException::CANCEL_UN_FAVOR());
        }

        $this->getFavoriteDao()->delete($favorite['id']);

        $courseFavoriteNum = $this->getFavoriteDao()->count(array(
            'courseId' => $courseId,
            'type' => 'openCourse',
        ));

        return $courseFavoriteNum;
    }

    public function getFavoriteByUserIdAndCourseId($userId, $courseId, $type)
    {
        return $this->getFavoriteDao()->getByUserIdAndCourseId($userId, $courseId, $type);
    }

    public function getLessonItems($courseId)
    {
        $lessons = $this->searchLessons(array('courseId' => $courseId), array('seq' => 'ASC'), 0, 1);

        $items = array();

        foreach ($lessons as $lesson) {
            $lesson['itemType'] = 'lesson';
            $items["lesson-{$lesson['id']}"] = $lesson;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
    }

    /**
     * open_course_lesson.
     */
    public function getLesson($id)
    {
        return $this->getOpenCourseLessonDao()->get($id);
    }

    public function findLessonsByIds(array $ids)
    {
        return $this->getOpenCourseLessonDao()->findByIds($ids);
    }

    public function findLessonsByCourseId($courseId)
    {
        return $this->getOpenCourseLessonDao()->findByCourseId($courseId);
    }

    public function searchLessons($condition, $orderBy, $start, $limit)
    {
        return $this->getOpenCourseLessonDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countLessons($conditions)
    {
        return $this->getOpenCourseLessonDao()->count($conditions);
    }

    public function createLesson($lesson)
    {
        $lesson = ArrayToolkit::filter($lesson, array(
            'courseId' => 0,
            'chapterId' => 0,
            'seq' => 0,
            'free' => 0,
            'title' => '',
            'summary' => '',
            'type' => 'text',
            'media' => array(),
            'content' => '',
            'mediaId' => 0,
            'mediaName' => '',
            'mediaUri' => '',
            'length' => 0,
            'startTime' => 0,
            'giveCredit' => 0,
            'requireCredit' => 0,
            'liveProvider' => 'none',
            'copyId' => 0,
            'testMode' => 'normal',
            'testStartTime' => 0,
            'status' => 'unpublished',
            'mediaSource' => '',
        ));
        $lesson['replayStatus'] = 'ungenerated';

        if (!ArrayToolkit::requireds($lesson, array('courseId', 'title', 'type'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (empty($lesson['courseId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $course = $this->getCourse($lesson['courseId'], true);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        if (!in_array($lesson['type'], array('video', 'liveOpen', 'open'))) {
            $this->createNewException(OpenCourseException::LESSON_TYPE_INVALID());
        }

        $this->fillLessonMediaFields($lesson);

        if (isset($fields['title'])) {
            $fields['title'] = $this->purifyHtml($fields['title']);
        }

        $lesson['status'] = 'unpublished';
        $lesson['number'] = $this->_getNextLessonNumber($lesson['courseId']);
        $lesson['seq'] = $this->_getNextCourseItemSeq($lesson['courseId']);
        $lesson['userId'] = $this->getCurrentUser()->id;
        $lesson['createdTime'] = time();

        if ('liveOpen' == $lesson['type']) {
            $lesson['endTime'] = $lesson['startTime'] + $lesson['length'] * 60;
        }

        $lesson = $this->getOpenCourseLessonDao()->create($lesson);

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', 1);
        }

        $this->dispatchEvent('open.course.lesson.create', array('lesson' => $lesson));

        return $lesson;
    }

    public function updateLesson($courseId, $lessonId, $fields)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        $fields = ArrayToolkit::filter($fields, array(
            'title' => '',
            'summary' => '',
            'content' => '',
            'media' => array(),
            'mediaId' => 0,
            'mediaSource' => '',
            'mediaName' => '',
            'mediaUri' => '',
            'number' => 0,
            'seq' => 0,
            'chapterId' => 0,
            'free' => 0,
            'length' => 0,
            'startTime' => 0,
            'giveCredit' => 0,
            'requireCredit' => 0,
            'homeworkId' => 0,
            'exerciseId' => 0,
            'testMode' => 'normal',
            'testStartTime' => 0,
            'replayStatus' => 'ungenerated',
            'status' => 'unpublished',
            'materialNum' => 0,
        ));

        if (isset($fields['title'])) {
            $fields['title'] = $this->purifyHtml($fields['title']);
        }

        $fields['type'] = $lesson['type'];

        if ('liveOpen' == $fields['type'] && isset($fields['startTime'])) {
            $fields['endTime'] = $fields['startTime'] + $fields['length'] * 60;
        }

        if (array_key_exists('media', $fields)) {
            $this->fillLessonMediaFields($fields);
        }

        $updatedLesson = $this->getOpenCourseLessonDao()->update($lessonId, $fields);

        $updatedLesson['fields'] = $lesson;
        $this->dispatchEvent('open.course.lesson.update', array('lesson' => $updatedLesson, 'sourceLesson' => $lesson));

        return $updatedLesson;
    }

    public function waveCourseLesson($id, $field, $diff)
    {
        return $this->getOpenCourseLessonDao()->wave(array($id), array($field => $diff));
    }

    public function deleteLesson($id)
    {
        $lesson = $this->getLesson($id);

        $result = $this->getOpenCourseLessonDao()->delete($id);

        $this->dispatchEvent('open.course.lesson.delete', array('lesson' => $lesson));

        return $result;
    }

    public function generateLessonVideoReplay($courseId, $lessonId, $fileId)
    {
        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        $file = $this->getUploadFileService()->getFile($fileId);
        if (!$file) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $lessonFields = array(
            'mediaId' => $file['id'],
            'mediaName' => $file['filename'],
            'mediaSource' => 'self',
            'replayStatus' => 'videoGenerated',
        );

        $updatedLesson = $this->getOpenCourseLessonDao()->update($lessonId, $lessonFields);

        $this->dispatchEvent('open.course.lesson.generate.video.replay', array('lesson' => $updatedLesson));

        return $lesson;
    }

    public function getCourseLesson($courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseLessonDao()->get($lessonId);

        if (empty($lesson) || ($lesson['courseId'] != $courseId)) {
            return null;
        }

        return $lesson;
    }

    public function getNextLesson($courseId, $lessonId)
    {
        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        $conditions = array(
            'number' => $lesson['number'] + 1,
            'courseId' => $courseId,
        );
        $orderBy = array('seq' => 'ASC');
        $nextLessons = $this->searchLessons($conditions, $orderBy, 0, 1);

        return array_shift($nextLessons);
    }

    public function publishLesson($courseId, $lessonId)
    {
        $course = $this->tryManageOpenCourse($courseId);

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        $publishedLesson = $this->getOpenCourseLessonDao()->update($lesson['id'], array('status' => 'published'));

        $this->dispatchEvent('open.course.lesson.publish', $publishedLesson);

        return $publishedLesson;
    }

    public function unpublishLesson($courseId, $lessonId)
    {
        $course = $this->tryManageOpenCourse($courseId);

        $lesson = $this->getCourseLesson($course['id'], $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        $lesson = $this->getOpenCourseLessonDao()->update($lesson['id'], array('status' => 'unpublished'));

        $this->dispatchEvent('open.course.lesson.unpublish', array('lesson' => $lesson));

        return $lesson;
    }

    public function resetLessonMediaId($lessonId)
    {
        $lesson = $this->getLesson($lessonId);
        if ($lesson) {
            $this->getOpenCourseLessonDao()->update($lesson['id'], array('mediaId' => 0));

            return true;
        }

        return false;
    }

    public function sortCourseItems($courseId, array $itemIds)
    {
        $items = $this->getLessonItems($courseId);
        $existedItemIds = array_keys($items);

        if (count($itemIds) != count($existedItemIds)) {
            $this->createNewException(OpenCourseException::ITEMIDS_INVALID());
        }

        $diffItemIds = array_diff($itemIds, array_keys($items));

        if (!empty($diffItemIds)) {
            $this->createNewException(OpenCourseException::ITEMIDS_INVALID());
        }

        $lessonNum = $seq = 0;

        foreach ($itemIds as $itemId) {
            ++$seq;
            list($type) = explode('-', $itemId);
            ++$lessonNum;

            $item = $items[$itemId];
            $fields = array('number' => $lessonNum, 'seq' => $seq);

            if ($fields['number'] != $item['number'] || $fields['seq'] != $item['seq']) {
                $this->updateLesson($courseId, $item['id'], $fields);
            }
        }
    }

    public function liveLessonTimeCheck($courseId, $lessonId, $startTime, $length)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(OpenCourseException::NOTFOUND_OPENCOURSE());
        }

        $thisStartTime = $thisEndTime = 0;

        $conditions = array();

        if ($lessonId) {
            $liveLesson = $this->getCourseLesson($course['id'], $lessonId);
            $conditions['lessonIdNotIn'] = array($lessonId);
        } else {
            $lessonId = '';
        }

        $startTime = is_numeric($startTime) ? $startTime : strtotime($startTime);
        $endTime = $startTime + $length * 60;

        $conditions['courseId'] = $courseId;
        $conditions['startTimeGreaterThan'] = $startTime;
        $conditions['endTimeLessThan'] = $endTime;

        $thisLessons = $this->getOpenCourseLessonDao()->findTimeSlotOccupiedLessonsByCourseId($courseId, $startTime, $endTime, $lessonId);

        if (($length / 60) > 8) {
            return array('error_timeout', '时长不能超过8小时！');
        }

        if ($thisLessons) {
            return array('error_occupied', '该时段内已有直播课时存在，请调整直播开始时间');
        }

        return array('success', '');
    }

    public function findFinishedLivesWithinTwoHours()
    {
        return $this->getOpenCourseLessonDao()->findFinishedLivesWithinTwoHours();
    }

    public function updateLiveStatus($id, $status)
    {
        $lesson = $this->getLesson($id);
        if (empty($lesson)) {
            return;
        }

        if (!in_array($status, array(EdusohoLiveClient::LIVE_STATUS_LIVING, EdusohoLiveClient::LIVE_STATUS_CLOSED, EdusohoLiveClient::LIVE_STATUS_PAUSE))) {
            $this->createNewException(OpenCourseException::STATUS_INVALID());
        }

        $updateLesson = $this->getOpenCourseLessonDao()->update($lesson['id'], array('progressStatus' => $status));
        $this->getLogService()->info(AppLoggerConstant::OPEN_COURSE, 'update_live_status', "公开课修改直播进行状态，由‘{$lesson['progressStatus']}’改为‘{$status}’", array('preLesson' => $lesson, 'newLesson' => $updateLesson));

        return $updateLesson;
    }

    /**
     * open_course_member.
     */
    public function getMember($id)
    {
        return $this->getOpenCourseMemberDao()->get($id);
    }

    public function getCourseMember($courseId, $userId)
    {
        return $this->getOpenCourseMemberDao()->getByUserIdAndCourseId($courseId, $userId);
    }

    public function getCourseMemberByIp($courseId, $ip)
    {
        return $this->getOpenCourseMemberDao()->getByIpAndCourseId($courseId, $ip);
    }

    public function getCourseMemberByMobile($courseId, $mobile)
    {
        return $this->getOpenCourseMemberDao()->getByMobileAndCourseId($courseId, $mobile);
    }

    public function findMembersByCourseIds($courseIds)
    {
        return $this->getOpenCourseMemberDao()->findByCourseIds($courseIds);
    }

    public function countMembers($conditions)
    {
        return $this->getOpenCourseMemberDao()->count($conditions);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        return $this->getOpenCourseMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function setCourseTeachers($courseId, $teachers)
    {
        $teacherMembers = array();

        foreach (array_values($teachers) as $index => $teacher) {
            if (empty($teacher['id'])) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $user = $this->getUserService()->getUser($teacher['id']);

            if (empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }

            $teacherMembers[] = array(
                'courseId' => $courseId,
                'userId' => $user['id'],
                'role' => 'teacher',
                'seq' => $index,
                'isVisible' => empty($teacher['isVisible']) ? 0 : 1,
                'createdTime' => time(),
            );
        }

        $existTeacherMembers = $this->findCourseTeachers($courseId);

        foreach ($existTeacherMembers as $member) {
            $this->getOpenCourseMemberDao()->delete($member['id']);
        }

        $visibleTeacherIds = array();

        foreach ($teacherMembers as $member) {
            $existMember = $this->getCourseMember($courseId, $member['userId']);

            if ($existMember) {
                $this->getOpenCourseMemberDao()->delete($existMember['id']);
            }

            $member = $this->getOpenCourseMemberDao()->create($member);

            if ($member['isVisible']) {
                $visibleTeacherIds[] = $member['userId'];
            }
        }

        $fields = array('teacherIds' => $visibleTeacherIds);
        $course = $this->updateCourse($courseId, $fields);
    }

    public function createMember($member)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $member['userId'] = $user['id'];
            $member['mobile'] = isset($user['verifiedMobile']) ? $user['verifiedMobile'] : '';
        } else {
            $member['userId'] = 0;
        }

        $member['createdTime'] = time();

        $newMember = $this->getOpenCourseMemberDao()->create($member);

        $this->dispatchEvent('open.course.member.create', array('argument' => $member, 'newMember' => $newMember));

        return $newMember;
    }

    public function updateMember($id, $member)
    {
        $member = ArrayToolkit::filter($member, array(
            'userId' => 0,
            'learnedNum' => '',
            'learnTime' => '',
            'role' => '',
            'ip' => '',
            'lastEnterTime' => 0,
            'mobile' => '',
            'seq' => 0,
            'isVisible' => 1,
            'isNotified' => 0,
        ));

        return $this->getOpenCourseMemberDao()->update($id, $member);
    }

    public function deleteMember($id)
    {
        return $this->getOpenCourseMemberDao()->delete($id);
    }

    public function getTodayOpenLiveCourseNumber()
    {
        $user = $this->getCurrentUser();
        $openLiveCourseNumber = 0;
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $openLessons = $this->searchLessons(
            array('type' => 'liveOpen', 'startTimeGreaterThan' => $beginToday, 'endTimeLessThan' => $endToday, 'status' => 'published'),
            array(),
            0,
            PHP_INT_MAX
        );
        foreach ($openLessons as $openLesson) {
            $members = $this->searchMembers(array('courseId' => $openLesson['courseId'], 'role' => 'teacher'), array(), 0, PHP_INT_MAX);
            $userIds = ArrayToolkit::column($members, 'userId');
            if (empty($userIds) || !in_array($user['id'], $userIds)) {
                continue;
            }
            $openCourse = $this->getCourse($openLesson['courseId']);
            if (!empty($openCourse) && 'published' == $openCourse['status']) {
                $openLiveCourseNumber = $openLiveCourseNumber + 1;
            }
        }

        return $openLiveCourseNumber;
    }

    public function findOpenLiveCourse($conditions, $userId)
    {
        $openLiveCourses = array();
        $openLessons = $this->searchLessons(
            array('type' => 'liveOpen', 'startTimeGreaterThan' => $conditions['startTime_GE'], 'endTimeLessThan' => $conditions['endTime_LT'], 'status' => 'published'),
            array(),
            0,
            PHP_INT_MAX
        );
        foreach ($openLessons as $openLesson) {
            $members = $this->searchMembers(array('courseId' => $openLesson['courseId'], 'role' => 'teacher'), array(), 0, PHP_INT_MAX);
            $userIds = ArrayToolkit::column($members, 'userId');
            if (empty($userIds) || !in_array($userId, $userIds)) {
                continue;
            }
            $openCourse = $this->getCourse($openLesson['courseId']);
            if (!empty($openCourse) && 'published' == $openCourse['status']) {
                $openLiveCourse = array(
                    'title' => $openCourse['title'],
                    'event' => $openCourse['title'],
                    'id' => $openCourse['id'],
                    'startTime' => date('Y-m-d H:i:s', $openLesson['startTime']),
                    'endTime' => date('Y-m-d H:i:s', $openLesson['endTime']),
                    'date' => date('w', $openLesson['startTime']),
                );
                array_push($openLiveCourses, $openLiveCourse);
            }
        }

        return $openLiveCourses;
    }

    protected function deleteLessonsByCourseId($courseId)
    {
        $lessons = $this->findLessonsByCourseId($courseId);
        $this->getOpenCourseLessonDao()->deleteByCourseId($courseId);

        if ($lessons) {
            foreach ($lessons as $key => $lesson) {
                $this->dispatchEvent('open.course.lesson.delete', array('lesson' => $lesson));
            }
        }
    }

    protected function fillLessonMediaFields(&$lesson)
    {
        if ('liveOpen' == $lesson['type'] && 'videoGenerated' != $lesson['replayStatus']) {
            $lesson['mediaName'] = '';
            $lesson['mediaSource'] = '';
            $lesson['mediaUri'] = '';
        } elseif ('self' == $lesson['mediaSource']) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            $lesson['mediaName'] = $file['filename'];
            $lesson['mediaSource'] = 'self';
            $lesson['mediaUri'] = '';
        }
        unset($lesson['media']);

        return $lesson;
    }

    protected function _filterCourseFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title' => '',
            'subtitle' => '',
            'about' => '',
            'categoryId' => 0,
            'startTime' => 0,
            'endTime' => 0,
            'tags' => '',
            'locationId' => 0,
            'address' => '',
            'locked' => 0,
            'hitNum' => 0,
            'likeNum' => 0,
            'postNum' => 0,
            'status' => 'draft',
            'lessonNum' => 0,
            'smallPicture' => '',
            'middlePicture' => '',
            'largePicture' => '',
            'teacherIds' => array(),
            'recommended' => 0,
            'recommendedSeq' => 0,
            'recommendedTime' => 0,
            'studentNum' => 0,
            'updateTime' => time(),
            'orgCode' => '1.',
            'orgId' => '1',
        ));

        if (isset($fields['tags'])) {
            if (!empty($fields['tags'])) {
                $fields['tags'] = explode(',', $fields['tags']);
                $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
                array_walk($fields['tags'], function (&$item, $key) {
                    $item = (int) $item['id'];
                });
            } else {
                $fields['tags'] = array();
            }
        }

        if (isset($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'], true);
        }

        $fields = $this->fillOrgId($fields);

        return $fields;
    }

    protected function hasOpenCourseManagerRole($courseId, $userId)
    {
        if ($this->getUserService()->hasAdminRoles($userId)) {
            return true;
        }

        $member = $this->getCourseMember($courseId, $userId);

        if ($member && ('teacher' == $member['role'])) {
            return true;
        }

        return false;
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        if (isset($conditions['creator']) && !empty($conditions['creator'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['creator']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['creator']);
        }

        if (isset($conditions['categoryId']) && !empty($conditions['categoryId'])) {
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            unset($conditions['categoryId']);
        }

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    private function _getNextLessonNumber($courseId)
    {
        $lessonCount = $this->countLessons(array('courseId' => $courseId));

        return $lessonCount + 1;
    }

    private function _getNextCourseItemSeq($courseId)
    {
        $lessonMaxSeq = $this->getOpenCourseLessonDao()->getLessonMaxSeqByCourseId($courseId);

        return $lessonMaxSeq + 1;
    }

    private function _deleteNotUsedPictures($course)
    {
        $oldPictures = array(
            'smallPicture' => $course['smallPicture'] ? $course['smallPicture'] : null,
            'middlePicture' => $course['middlePicture'] ? $course['middlePicture'] : null,
            'largePicture' => $course['largePicture'] ? $course['largePicture'] : null,
        );

        $courseCount = $this->countCourses(array('smallPicture' => $course['smallPicture']));

        if ($courseCount <= 1) {
            $fileService = $this->getFileService();
            array_map(function ($oldPicture) use ($fileService) {
                if (!empty($oldPicture)) {
                    $fileService->deleteFileByUri($oldPicture);
                }
            }, $oldPictures);
        }
    }

    public function findCourseTeachers($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'role' => 'teacher',
        );
        $orders = array('seq' => 'DESC', 'createdTime' => 'DESC');

        return $this->getOpenCourseMemberDao()->search($conditions, $orders, 0, 100);
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }

    /**
     * @return OpenCourseDao
     */
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
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->createDao('Course:FavoriteDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getLiveCourseService()
    {
        return $this->createService('OpenCourse:LiveCourseService');
    }
}
