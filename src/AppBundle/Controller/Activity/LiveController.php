<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\LiveroomController;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LiveReplayService;
use Biz\Course\Service\MemberService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class LiveController extends BaseActivityController implements ActivityActionInterface
{
    public function previewAction(Request $request, $task)
    {
        return $this->render('activity/no-preview.html.twig');
    }

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
            'roomCreated' => $live['roomCreated'],
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, true);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $id);

        return $this->render('activity/live/modal.html.twig', array(
            'activity' => $this->formatTimeFields($activity),
            'courseId' => $courseId,
            'taskId' => $task['id'],
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
            return $this->createMessageResponse('info', 'message_response.login_forget.message', null, 3000, $this->generateUrl('login'));
        }

        $result = $this->getActivityService()->checkLiveStatus($courseId, $activityId);
        if (!$result['result']) {
            return $this->createMessageResponse('info', $result['message']);
        }

        $activity = $this->getActivityService()->getActivity($activityId, $fetchMedia = true);

        $params = array();
        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            $teachers = $this->getCourseService()->findTeachersByCourseId($courseId);
            $teachers = ArrayToolkit::index($teachers, 'userId');

            $course = $this->getCourseService()->getCourse($courseId);
            $teacherId = array_shift($course['teacherIds']);

            $teacher = $teachers[$teacherId];

            if ($teacher['userId'] == $user['id']) {
                $params['role'] = 'teacher';
            } else {
                $params['role'] = 'speaker';
            }
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            return $this->createMessageResponse('info', 'message_response.not_student_cannot_join_live.message');
        }

        $params['id'] = $user['id'];
        $params['nickname'] = $user['nickname'];

        /**
         * @var int
         *          last record: 2017-12-12
         *          '1'=>'vhall',
         *          '2'=>'soooner',
         *          '3'=>'sanmang',
         *          '4'=>'gensee',
         *          '5'=>'longinus',
         *          '6'=>'training',
         *          '7'=>'talkFun',
         *          '8'=>'athena', //ES直播
         */
        $provider = empty($activity['ext']['liveProvider']) ? 0 : $activity['ext']['liveProvider'];

        return $this->forward('AppBundle:Liveroom:_entry', array(
            'roomId' => $activity['ext']['liveId'],
            'params' => array('courseId' => $courseId, 'activityId' => $activityId, 'provider' => $provider),
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
        if ('live' !== $activity['mediaType']) {
            return $this->createJsonResponse(array('success' => true, 'status' => 'not_live'));
        }
        $now = time();
        if ($activity['startTime'] > $now) {
            return $this->createJsonResponse(array('success' => true, 'status' => 'not_start'));
        }

        if ($this->validTaskLearnStat($request, $activity['id'])) {
            //当前业务逻辑：看过即视为完成
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
            $eventName = $request->query->get('eventName');
            $data = $request->query->get('data');
            if (!empty($eventName)) {
                $this->getTaskService()->trigger($task['id'], $eventName, $data);
            }
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

            if ('start' == $taskResult['status']) {
                $this->getActivityService()->trigger($activityId, 'finish', array('taskId' => $task['id']));
                $this->getTaskService()->finishTaskResult($task['id']);
            }
        }

        $status = $activity['endTime'] < $now ? 'live_end' : 'on_live';

        return $this->createJsonResponse(array('success' => true, 'status' => $status));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        return $this->render('activity/live/finish-condition.html.twig', array());
    }

    public function replayEntryAction(Request $request, $courseId, $activityId, $replayId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId);

        return $this->render('live-course/classroom.html.twig', array(
            'lesson' => $activity,
            'url' => $this->generateUrl('live_activity_replay_url', array(
                'courseId' => $courseId,
                'replayId' => $replayId,
                'activityId' => $activityId,
            )),
        ));
    }

    public function replayUrlAction(Request $request, $courseId, $activityId, $replayId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId);
        $replay = $this->getLiveReplayService()->getReplay($replayId);

        if ((bool) $replay['hidden']) {
            throw $this->createNotFoundException('replay not found');
        }

        $sourceActivityId = empty($activity['copyId']) ? $activity['id'] : $activity['copyId'];
        $sourceActivity = $this->getActivityService()->getActivity($sourceActivityId, true);
        $result = $this->getLiveReplayService()->entryReplay($replay['id'], $sourceActivity['ext']['liveId'], $sourceActivity['ext']['liveProvider'],
            $request->isSecure());

        if (!empty($result) && !empty($result['resourceNo'])) {
            $result['url'] = $this->generateUrl('es_live_room_replay_show', array(
                'targetType' => LiveroomController::LIVE_COURSE_TYPE,
                'targetId' => $activity['fromCourseId'],
                'lessonId' => $activity['id'],
                'replayId' => $replay['id'],
            ));
        }

        return $this->createJsonResponse(array(
            'url' => empty($result['url']) ? '' : $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null,
            'error' => isset($result['error']) ? $result['error'] : null,
        ));
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

    // Refactor: redesign course_lesson_replay table
    protected function _getLiveReplays($activity)
    {
        if ($activity['ext']['replayStatus'] === LiveReplayService::REPLAY_GENERATE_STATUS) {
            $copyId = empty($activity['copyId']) ? $activity['id'] : $activity['copyId'];

            $replays = $this->getLiveReplayService()->findReplayByLessonId($copyId);

            $replays = array_filter($replays, function ($replay) {
                // 过滤掉被隐藏的录播回放
                return !empty($replay) && !(bool) $replay['hidden'];
            });

            $self = $this;
            $replays = array_map(function ($replay) use ($activity, $self) {
                $replay['url'] = $self->generateUrl('live_activity_replay_entry', array(
                    'courseId' => $activity['fromCourseId'],
                    'activityId' => $activity['id'],
                    'replayId' => $replay['id'],
                ));

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
