<?php

namespace Biz\OpenCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\LiveActivityException;
use Biz\BaseService;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\LiveCourseService;
use Biz\User\UserException;
use Biz\System\Service\SettingService;
use Biz\Util\EdusohoLiveClient;
use Topxia\Service\Common\ServiceKernel;

class LiveCourseServiceImpl extends BaseService implements LiveCourseService
{
    const LIVE_STARTTIME_DIFF_SECONDS = 7200;
    const LIVE_ENDTIME_DIFF_SECONDS = 7200;

    private $liveClient = null;

    public function createLiveRoom($course, $lesson, $routes)
    {
        $liveParams = $this->_filterParams($course['teacherIds'], $lesson, $routes, 'add');

        $live = $this->createLiveClient()->createLive($liveParams);

        if (empty($live)) {
            $this->createNewException(LiveActivityException::CREATE_LIVEROOM_FAILED());
        }

        if (isset($live['error'])) {
            throw $this->createServiceException($live['error']);
        }

        return $live;
    }

    public function editLiveRoom($course, $lesson, $routes)
    {
        $liveParams = $this->_filterParams($course['teacherIds'], $lesson, $routes, 'update');

        return $this->createLiveClient()->updateLive($liveParams);
    }

    public function entryLive($params)
    {
        return $this->createLiveClient()->entryLive($params);
    }

    public function checkLessonStatus($lesson)
    {
        if (empty($lesson)) {
            return array('result' => false, 'message' => '课时不存在！');
        }

        if (empty($lesson['mediaId'])) {
            return array('result' => false, 'message' => '直播教室不存在！');
        }

        if ($lesson['startTime'] - time() > self::LIVE_STARTTIME_DIFF_SECONDS) {
            return array('result' => false, 'message' => '直播还没开始!');
        }

        if ($this->checkLiveFinished($lesson)) {
            return array('result' => false, 'message' => '直播已结束!');
        }

        return array('result' => true, 'message' => '');
    }

    public function checkCourseUserRole($course, $lesson)
    {
        $role = '';
        $user = $this->getCurrentUser();

        if (!$user->isLogin() && 'liveOpen' == $lesson['type']) {
            return 'student';
        } elseif (!$user->isLogin() && 'liveOpen' != $lesson['type']) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $courseMember = $this->getOpenCourseService()->getCourseMember($lesson['courseId'], $user['id']);

        if (!$courseMember) {
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

    public function isLiveFinished($lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (empty($lesson) || 'liveOpen' != $lesson['type']) {
            return true;
        }

        if ($this->checkLiveFinished($lesson)) {
            return true;
        }

        if (EdusohoLiveClient::LIVE_STATUS_CLOSED == $lesson['progressStatus']) {
            return true;
        }

        return false;
    }

    protected function checkLiveFinished($lesson)
    {
        $isEsLive = EdusohoLiveClient::isEsLive($lesson['liveProvider']);
        $endLeftSeconds = time() - $lesson['endTime'];

        //ES直播结束时间2小时后就自动结束，第三方直播以直播结束时间为准
        $thirdLiveFinished = $endLeftSeconds > 0 && !$isEsLive;
        $esLiveFinished = $isEsLive && $endLeftSeconds > self::LIVE_ENDTIME_DIFF_SECONDS;

        return $thirdLiveFinished || $esLiveFinished;
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

    protected function createLiveClient()
    {
        if (empty($this->liveClient)) {
            $this->liveClient = new EdusohoLiveClient();
        }

        return $this->liveClient;
    }

    private function _getSpeaker($courseTeachers)
    {
        $speakerId = current($courseTeachers);
        $speaker = $speakerId ? $this->getUserService()->getUser($speakerId) : null;

        return $speaker ? $speaker['nickname'] : '老师';
    }

    private function _filterParams($courseTeacherIds, $lesson, $routes, $actionType = 'add')
    {
        $params = array(
            'summary' => isset($lesson['summary']) ? $lesson['summary'] : '',
            'title' => $lesson['title'],
            'type' => $lesson['type'],
            'speaker' => $this->_getSpeaker($courseTeacherIds),
            'authUrl' => $routes['authUrl'],
            'jumpUrl' => $routes['jumpUrl'],
        );

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

        return $params;
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

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
