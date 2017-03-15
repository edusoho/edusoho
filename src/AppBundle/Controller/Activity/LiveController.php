<?php

namespace AppBundle\Controller\Activity;

use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\TaskResultService;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\LiveReplayService;
use Symfony\Component\HttpFoundation\Request;

class LiveController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $live = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);
        $activity['ext'] = $live;

        $format = 'Y-m-d H:i';
        if (isset($activity['startTime'])) {
            $activity['startTimeFormat'] = date($format, $activity['startTime']);
        }
        if (isset($activity['endTime'])) {
            $activity['endTimeFormat'] = date($format, $activity['endTime']);
        }
        $activity['nowDate'] = time();

        if ($activity['ext']['replayStatus'] == LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS) {
            $activity['replays'] = array($this->_getLiveVideoReplay($activity));
        } else {
            $activity['replays'] = $this->_getLiveReplays($activity);
        }

        if ($this->getCourseMemberService()->isCourseTeacher($activity['fromCourseId'], $this->getUser()->id)) {
            $activity['isTeacher'] = $this->getUser()->isTeacher();
        }

        $summary = $activity['remark'];
        unset($activity['remark']);

        $this->freshTaskLearnStat($request, $activity['id']);

        return $this->render('activity/live/show.html.twig', array(
            'activity' => $activity,
            'summary' => $summary,
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, true);

        return $this->render('activity/live/modal.html.twig', array(
            'activity' => $this->formatTimeFields($activity),
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/live/modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function liveEntryAction($courseId, $activityId)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $activity = $this->getActivityService()->getActivity($activityId, $fetchMedia = true);

        if (empty($activity)) {
            return $this->createMessageResponse('info', '直播任务不存在！');
        }
        if ($activity['fromCourseId'] != $courseId) {
            return $this->createMessageResponse('info', '参数非法！');
        }

        if (empty($activity['ext']['liveId'])) {
            return $this->createMessageResponse('info', '直播教室不存在！');
        }

        if ($activity['startTime'] - time() > 7200) {
            return $this->createMessageResponse('info', '直播还没开始!');
        }

        if ($activity['endTime'] < time()) {
            return $this->createMessageResponse('info', '直播已结束!');
        }

        $params = array();
        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            $teachers = $this->getCourseService()->findTeachersByCourseId($courseId);
            $teacher = array_shift($teachers);

            if ($teacher['userId'] == $user['id']) {
                $params['role'] = 'teacher';
            } else {
                $params['role'] = 'speaker';
            }
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            return $this->createMessageResponse('info', '您不是课程学员，不能参加直播！');
        }

        $params['id'] = $user['id'];
        $params['nickname'] = $user['nickname'];

        return $this->forward('AppBundle:Liveroom:_entry', array(
            'roomId' => $activity['ext']['liveId'],
            'params' => array('courseId' => $courseId, 'activityId' => $activityId),
        ), $params);
    }

    public function liveReplayAction($courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId);
        $live = $this->getActivityService()->getActivityConfig('live')->get($activity['mediaId']);

        return $this->render('activity/live/replay-player.html.twig', array(
            'live' => $live,
        ));
    }

    public function triggerAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $activity = $this->getActivityService()->getActivity($activityId);
        if ($activity['mediaType'] !== 'live') {
            return $this->createJsonResponse(array('success' => true, 'status' => 'not_live'));
        }
        $now = time();
        if ($activity['startTime'] > $now) {
            return $this->createJsonResponse(array('success' => true, 'status' => 'not_start'));
        }

        if ($activity['endTime'] < $now) {
            return $this->createJsonResponse(array('success' => true, 'status' => 'live_end'));
        }

        if($this->validTaskLearnStat($request, $activity['id'])){
            //当前业务逻辑：看过即视为完成
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
            //如果尚未开始则标记为开始
            if (empty($taskResult)) {
                $this->getActivityService()->trigger($activityId, 'start', array('task' => $task));
            } elseif ($taskResult['status'] == 'start') {
                $this->getActivityService()->trigger($activityId, 'finish', array('taskId' => $task['id']));
                $this->getTaskService()->finishTaskResult($task['id']);
            }
        }

        return $this->createJsonResponse(array('success' => true, 'status' => 'on_live'));
    }

    public function finishConditionAction($activity)
    {
        return $this->render('activity/live/finish-condition.html.twig', array());
    }

    private function freshTaskLearnStat(Request $request, $activityId)
    {
        $key = 'activity.'.$activityId;
        $session = $request->getSession();
        $taskStore = $session->get($key, array());
        $taskStore['start'] = time();
        $taskStore['lastTriggerTime'] = 0;

        $session->set($key, $taskStore);
    }

    private function validTaskLearnStat(Request $request, $activityId)
    {
        $key = 'activity.'.$activityId;
        $session = $request->getSession($key);
        $taskStore = $session->get($key);

        if (!empty($taskStore)) {
            $now = time();
            //任务连续学习超过5小时则不再统计时长
            if ($now - $taskStore['start'] > 60 * 60 * 5) {
                return false;
            }
            //任务每分钟只允许触发一次，这里用55秒作为标准判断，以应对网络延迟
            if ($now - $taskStore['lastTriggerTime'] < 55) {
                return false;
            }
            $taskStore['lastTriggerTime'] = $now;
            $session->set($key, $taskStore);

            return true;
        }

        return false;
    }

    protected function _getLiveVideoReplay($activity, $ssl = false)
    {
        if ($activity['ext']['replayStatus'] == LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS) {
            $file = $this->getUploadFileService()->getFullFile($activity['ext']['mediaId']);

            return array(
                'url' => $this->generateUrl('task_live_replay_player', array(
                    'activityId' => $activity['id'],
                    'courseId' => $activity['fromCourseId'],
                )),
                'title' => $file['filename'],
            );
        } else {
            return array();
        }
    }

    protected function _getLiveReplays($activity, $ssl = false)
    {
        if ($activity['ext']['replayStatus'] == LiveReplayService::REPLAY_GENERATE_STATUS) {
            $replays = $this->getLiveReplayService()->findReplayByLessonId($activity['id']);

            $service = $this->getLiveReplayService();
            $self = $this;
            $replays = array_map(function ($replay) use ($service, $activity, $ssl, $self) {
                $result = $service->entryReplay($replay['id'], $activity['ext']['liveId'], $activity['ext']['liveProvider'], $ssl);

                if (!empty($result) && !empty($result['resourceNo'])) {
                    // ES Live
                    $replay['url'] = $self->generateUrl('global_file_player', array('globalId' => $replay['globalId']));
                } elseif (!empty($result['url'])) {
                    // Other Live
                    $replay['url'] = $result['url'];
                }

                return $replay;
            }, $replays);
        } else {
            $replays = array();
        }

        return $replays;
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    //int to datetime
    protected function formatTimeFields($fields)
    {
        $format = 'Y-m-d H:i';
        if (isset($fields['startTime'])) {
            if ($fields['startTime'] <= time() && $fields['ext']['roomCreated']) {
                $fields['timeDisabled'] = 1;
            }
            $fields['startTime'] = date($format, $fields['startTime']);
        }
        if (isset($fields['endTime'])) {
            $fields['endTime'] = date($format, $fields['endTime']);
        }

        return $fields;
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }
}
