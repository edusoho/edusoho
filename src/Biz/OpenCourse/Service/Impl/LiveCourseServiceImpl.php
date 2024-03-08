<?php

namespace Biz\OpenCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\LiveActivityException;
use Biz\BaseService;
use Biz\Live\Constant\LiveStatus;
use Biz\Live\Service\LiveService;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\LiveCourseService;
use Biz\System\Service\SettingService;
use Biz\User\UserException;
use Biz\Util\EdusohoLiveClient;
use Topxia\Service\Common\ServiceKernel;

class LiveCourseServiceImpl extends BaseService implements LiveCourseService
{
    const LIVE_STARTTIME_DIFF_SECONDS = 7200;

    private $liveClient = null;

    public function createLiveRoom($course, $lesson, $routes)
    {
        $liveParams = $this->_filterParams($course, $lesson, $routes, 'add');

        $live = $this->createLiveClient()->createLive($liveParams);

        if (empty($live)) {
            $this->createNewException(LiveActivityException::CREATE_LIVEROOM_FAILED());
        }

        if (isset($live['error'])) {
            throw $this->createServiceException($live['error'], 500);
        }

        return $live;
    }

    private function _filterParams($course, $lesson, $routes, $actionType = 'add')
    {
        $courseTeacherIds = $course['teacherIds'];
        $params = [
            'summary' => $lesson['summary'] ?? '',
            'title' => $lesson['title'],
            'type' => $lesson['type'],
            'speaker' => $this->_getSpeaker($courseTeacherIds),
            'authUrl' => $routes['authUrl'],
            'jumpUrl' => $routes['jumpUrl'],
        ];

        if ('add' == $actionType) {
            $params['liveLogoUrl'] = $this->_getLiveLogo();
            $params['startTime'] = $lesson['startTime'].'';
            $params['endTime'] = ($lesson['startTime'] + $lesson['length'] * 60).'';
        } elseif ('update' == $actionType) {
            $params['liveId'] = $lesson['mediaId'];
            $params['provider'] = $lesson['liveProvider'];

            if (isset($lesson['startTime']) && !empty($lesson['startTime'])) {
                $params['startTime'] = $lesson['startTime'];

                if (isset($lesson['length']) && !empty($lesson['length'])) {
                    $params['endTime'] = ($lesson['startTime'] + $lesson['length'] * 60).'';
                }
            }
        }
        $speakerId = current($courseTeacherIds) ?: $course['userId'];
        $liveAccount = $this->createLiveClient()->getLiveAccount();
        $params['teacherId'] = $this->getLiveService()->getLiveProviderTeacherId($speakerId, $liveAccount['provider']);

        return $params;
    }

    private function _getSpeaker($courseTeachers)
    {
        $speakerId = current($courseTeachers);
        $speaker = $speakerId ? $this->getUserService()->getUser($speakerId) : null;

        return $speaker ? $speaker['nickname'] : '老师';
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function _getLiveLogo()
    {
        $liveLogo = $this->getSettingService()->get('course');
        $liveLogoUrl = '';

        if (!empty($liveLogo) && !empty($liveLogo['live_logo'])) {
            $liveLogoUrl = ServiceKernel::instance()->getEnvVariable('baseUrl').'/'.$liveLogo['live_logo'];
        }

        return $liveLogoUrl;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function createLiveClient()
    {
        if (empty($this->liveClient)) {
            $this->liveClient = new EdusohoLiveClient();
        }

        return $this->liveClient;
    }

    /**
     * @return LiveService
     */
    protected function getLiveService()
    {
        return $this->createService('Live:LiveService');
    }

    public function editLiveRoom($course, $lesson, $routes)
    {
        $liveParams = $this->_filterParams($course, $lesson, $routes, 'update');

        return $this->createLiveClient()->updateLive($liveParams);
    }

    public function entryLive($params)
    {
        return $this->createLiveClient()->entryLive($params);
    }

    public function checkLessonStatus($lesson)
    {
        if (empty($lesson)) {
            return ['result' => false, 'message' => '课时不存在！'];
        }

        if (empty($lesson['mediaId'])) {
            return ['result' => false, 'message' => '直播教室不存在！'];
        }

        if ($lesson['startTime'] - time() > self::LIVE_STARTTIME_DIFF_SECONDS) {
            return ['result' => false, 'message' => '直播还没开始!'];
        }

        if (LiveStatus::CLOSED == $lesson['progressStatus']) {
            return ['result' => false, 'message' => '直播已结束!'];
        }

        return ['result' => true, 'message' => ''];
    }

    public function checkCourseUserRole($course, $lesson)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            if ('liveOpen' == $lesson['type']) {
                return 'student';
            }
            $this->createNewException(UserException::UN_LOGIN());
        }

        $courseMember = $this->getOpenCourseService()->getCourseMember($lesson['courseId'], $user['id']);
        if (!$courseMember) {
            if ('liveOpen' == $lesson['type']) {
                return 'student';
            }
            $this->createNewException(OpenCourseException::IS_NOT_MEMBER());
        }

        $role = 'student';
        $courseTeachers = $this->getOpenCourseService()->findCourseTeachers($lesson['courseId']);
        $courseTeachersIds = ArrayToolkit::column($courseTeachers, 'userId');
        $courseTeachers = ArrayToolkit::index($courseTeachers, 'userId');

        if (in_array($user['id'], $courseTeachersIds)) {
            $teacherId = array_shift($course['teacherIds']);
            $firstTeacher = $courseTeachers[$teacherId];
            if ($firstTeacher['userId'] == $user['id']) {
                $role = 'teacher';
            } else {
                $role = 'speaker';
            }
        }

        return $role;
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    public function isLiveFinished($lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (empty($lesson) || 'liveOpen' != $lesson['type']) {
            return true;
        }

        if (LiveStatus::CLOSED == $lesson['progressStatus']) {
            return true;
        }

        return false;
    }

    /**
     * only for mock.
     *
     * @param [type] $liveClient [description]
     */
    public function setLiveClient($liveClient)
    {
        return $this->liveClient = $liveClient;
    }
}
