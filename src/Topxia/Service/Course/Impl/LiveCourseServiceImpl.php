<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\EdusohoLiveClient;
use Topxia\Service\Course\LiveCourseService;

class LiveCourseServiceImpl extends BaseService implements LiveCourseService
{
    public function createLiveRoom($course, $lesson, $container)
    {
        $liveParams = $this->_filterParams($course['teacherIds'], $lesson, $container, 'add');

        $client = new EdusohoLiveClient();
        $live   = $client->createLive($liveParams);

        if (empty($live)) {
            throw new \RuntimeException('创建直播教室失败，请重试！');
        }

        if (isset($live['error'])) {
            throw new \RuntimeException($live['error']);
        }

        return $live;
    }

    public function editLiveRoom($course, $lesson, $container)
    {
        $liveParams = $this->_filterParams($course['teacherIds'], $lesson, $container, 'update');

        $client = new EdusohoLiveClient();
        $live   = $client->updateLive($liveParams);

        return $live;
    }

    public function entryLive($params)
    {
        $lesson = $this->getCourseService($courseType)->getLesson($lessonId);
        $client = new EdusohoLiveClient();
        $result = $client->entryLive($params);

        return $result;
    }

    public function entryReplay($lessonReplayId)
    {
        $lessonReplay = $this->getCourseService('course')->getCourseLessonReplay($lessonReplayId);
        $user         = $this->getCurrentUser();

        $lesson = $this->getCourseService($lessonReplay['type'])->getCourseLesson($lessonReplay['courseId'], $lessonReplay['lessonId']);

        $args = array(
            'liveId'   => $lesson["mediaId"],
            'replayId' => $lessonReplay["replayId"],
            'provider' => $lesson["liveProvider"],
            'user'     => $user->isLogin() ? $user['email'] : '',
            'nickname' => $user->isLogin() ? $user['nickname'] : 'guest'
        );

        $client = new EdusohoLiveClient();
        $result = $client->entryReplay($args);
        return $result;
    }

    public function checkLessonStatus($lesson)
    {
        if (empty($lesson)) {
            return array('result' => false, 'message' => '课时不存在！');
            //throw $this->createServiceException("课时不存在！");
        }

        if (empty($lesson['mediaId'])) {
            return array('result' => false, 'message' => '直播教室不存在！');
            //throw $this->createServiceException("直播教室不存在！");
        }

        if ($lesson['startTime'] - time() > 7200) {
            return array('result' => false, 'message' => '直播还没开始!');
            //throw $this->createServiceException("直播还没开始!");
        }

        if ($lesson['endTime'] < time()) {
            return array('result' => false, 'message' => '直播已结束!');
            //throw $this->createServiceException("直播已结束!");
        }

        return array('result' => true, 'message' => '');
    }

    public function checkCourseUserRole($lesson)
    {
        $role = '';
        $user = $this->getCurrentUser();

        if (!$user->isLogin() && $lesson['type'] == 'liveOpen') {
            return 'student';
        } elseif (!$user->isLogin() && $lesson['type'] != 'liveOpen') {
            throw $this->createServiceException("您还未登录，不能参加直播！");
        }

        $courseMember = $this->getCourseService($lesson['type'])->getCourseMember($lesson['courseId'], $user['id']);

        if (!$courseMember) {
            throw $this->createServiceException('您不是课程学员，不能参加直播！');
        }

        $role              = 'student';
        $courseTeachers    = $this->getCourseService($lesson['type'])->findCourseTeachers($lesson['courseId']);
        $courseTeachersIds = ArrayToolkit::column($courseTeachers, 'userId');

        if (in_array($user['id'], $courseTeachersIds)) {
            $firstTeacher = array_shift($courseTeachers);
            if ($firstTeacher['userId'] == $user['id']) {
                $role = 'teacher';
            } else {
                $role = 'speaker';
            }
        }

        return $role;
    }

    public function generateLessonReplay($course, $lesson)
    {
        $client     = new EdusohoLiveClient();
        $replayList = $client->createReplayList($lesson["mediaId"], "录播回放", $lesson["liveProvider"]);

        if (isset($replayList['error']) && !empty($replayList['error'])) {
            return $replayList;
        }

        $this->getCourseService('live')->deleteLessonReplayByLessonId($lesson['id'], $course['type']);

        if (isset($replayList['data']) && !empty($replayList['data'])) {
            $replayList = json_decode($replayList["data"], true);
        }

        foreach ($replayList as $key => $replay) {
            $fields = array(
                'courseId'    => $course['id'],
                'lessonId'    => $lesson['id'],
                'title'       => $replay['subject'],
                'replayId'    => $replay['id'],
                'userId'      => $this->getCurrentUser()->id,
                'createdTime' => time(),
                'type'        => $course['type']
            );

            $courseLessonReplay = $this->getCourseService('live')->addCourseLessonReplay($fields);
        }

        $lessonFields = array(
            "replayStatus" => "generated"
        );

        $lesson = $this->getCourseService($course['type'])->updateLesson($course['id'], $lesson['id'], $lessonFields);

        $this->dispatchEvent("course.lesson.generate.replay", array('courseId' => $course['id'], 'lessonId' => $lesson['id']));

        return $replayList;
    }

    private function _getSpeaker($courseTeachers)
    {
        $speakerId = current($courseTeachers);
        $speaker   = $speakerId ? $this->getUserService()->getUser($speakerId) : null;
        return $speaker ? $speaker['nickname'] : '老师';
    }

    private function _filterParams($courseTeacherIds, $lesson, $container, $actionType = 'add')
    {
        $params = array(
            'summary' => isset($lesson['summary']) ? $lesson['summary'] : '',
            'title'   => $lesson['title'],
            'speaker' => $this->_getSpeaker($courseTeacherIds),
            'authUrl' => $container->get('router')->generate('live_auth', array(), true),
            'jumpUrl' => $container->get('router')->generate('live_jump', array('id' => $lesson['courseId']), true)
        );

        if ($actionType == 'add') {
            $params['liveLogoUrl'] = $this->_getLiveLogo();
            $params['startTime']   = $lesson['startTime'].'';
            $params['endTime']     = ($lesson['startTime'] + $lesson['length'] * 60).'';
        } elseif ($actionType == 'update') {
            $params['liveId']   = $lesson['mediaId'];
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
        $liveLogo    = $this->getSettingService()->get('course');
        $liveLogoUrl = "";

        if (!empty($liveLogo) && isset($liveLogo['live_logo']) && !empty($liveLogo["live_logo"])) {
            $liveLogoUrl = ServiceKernel::instance()->getEnvVariable('baseUrl')."/".$liveLogo["live_logo"];
        }

        return $liveLogoUrl;
    }

    protected function getCourseService($courseType)
    {
        if ($courseType == 'liveOpen') {
            return $this->createService('OpenCourse.OpenCourseService');
        } else {
            return $this->createService('Course.CourseService');
        }
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}
