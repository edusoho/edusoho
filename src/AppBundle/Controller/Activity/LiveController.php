<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\LiveroomController;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LiveReplayService;
use Biz\Course\Service\MemberService;
use Biz\File\Service\UploadFileService;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
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

        if (LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS == $activity['ext']['replayStatus']) {
            $activity['replays'] = [$this->_getLiveVideoReplay($activity)];
        } else {
            $activity['replays'] = $this->_getLiveReplays($activity);
        }

        if ($this->getCourseMemberService()->isCourseTeacher($activity['fromCourseId'], $this->getUser()->id)) {
            $activity['isTeacher'] = $this->getUser()->isTeacher();
        }

        $summary = $activity['remark'];
        unset($activity['remark']);

        return $this->render('activity/live/show.html.twig', [
            'activity' => $activity,
            'summary' => $summary,
            'roomCreated' => $live['roomCreated'],
        ]);
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, true);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $id);

        $canUpdateRoomType = $this->getLiveActivityService()->canUpdateRoomType($activity['startTime']);

        return $this->render('activity/live/modal.html.twig', [
            'activity' => $this->formatTimeFields($activity),
            'courseId' => $courseId,
            'taskId' => $task['id'],
            'canUpdateRoomType' => $canUpdateRoomType,
        ]);
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/live/modal.html.twig', [
            'courseId' => $courseId,
        ]);
    }

    public function liveEntryAction(Request $request, $courseId, $activityId)
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
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);

        $params = [];
        if ($this->getCourseMemberService()->isCourseMember($courseId, $user['id'])) {
            $params['role'] = $this->getCourseMemberService()->getUserLiveroomRoleByCourseIdAndUserId($courseId, $user['id']);
        } else {
            return $this->createMessageResponse('info', 'message_response.not_student_cannot_join_live.message');
        }

        $params['id'] = $user['id'];
        /*
         * displayName 用于直播间用户名展示
         */
        $params['displayName'] = $user['nickname'];
        $params['nickname'] = $user['nickname'].'_'.$user['id'];

        $liveGroup = $this->getMultiClassGroupService()->getLiveGroupByUserIdAndCourseId($user['id'], $courseId, $activity['ext']['liveId']);
        if (!empty($liveGroup)) {
            $params['groupCode'] = $liveGroup['live_code'];
        }

        /**
         * provider code in wiki
         */
        $provider = empty($activity['ext']['liveProvider']) ? 0 : $activity['ext']['liveProvider'];
        $this->freshTaskLearnStat($request, $activity['id']);

        return $this->forward('AppBundle:Liveroom:_entry', [
            'roomId' => $activity['ext']['liveId'],
            'params' => [
                'triggerEvent' => true,
                'courseId' => $courseId,
                'activityId' => $activityId,
                'taskId' => $task['id'],
                'provider' => $provider,
                'startTime' => $activity['startTime'],
                'endTime' => $activity['endTime'],
            ],
        ], $params);
    }

    /**
     * @param $courseId
     * @param $activityId
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     *                                                         自己上传的回放播放入口
     */
    public function liveReplayAction($courseId, $activityId)
    {
        $user = $this->getUser();
        $this->getCourseService()->tryTakeCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId);
        $live = $this->getActivityService()->getActivityConfig('live')->get($activity['mediaId']);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);

        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            $role = 'teacher';
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $role = 'student';
        } else {
            return $this->createMessageResponse('info', 'message_response.not_student_cannot_join_live.message');
        }

        return $this->render('activity/live/replay-player.html.twig', [
            'courseId' => $courseId,
            'activityId' => $activityId,
            'taskId' => $task['id'],
            'live' => $live,
            'mediaId' => $live['mediaId'],
            'role' => $role,
        ]);
    }

    /**
     * @param $mediaId
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     *                                                         自己上传的视频回放链接
     */
    public function liveReplayEntryAction(Request $request, $mediaId)
    {
        return $this->render('activity/live/replay-player-show.html.twig', [
            'mediaId' => $mediaId,
        ]);
    }

    public function triggerAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $activity = $this->getActivityService()->getActivity($activityId);
        if ('live' !== $activity['mediaType']) {
            return $this->createJsonResponse(['success' => true, 'status' => 'not_live']);
        }
        if ($this->validTaskLearnStat($request, $activity['id'])) {
            //当前业务逻辑：看过即视为完成
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
            if (empty($taskResult)) {
                $taskResult = $this->getTaskService()->startTask($task['id']);
            }
            $eventName = $request->query->get('eventName');
            $data = $request->query->get('data');
            if (!empty($eventName)) {
                $this->getTaskService()->trigger($task['id'], 'doing', $data);
            }

            if (in_array($taskResult['status'], ['start', 'doing'])) {
                if ('doing' == $taskResult['status']) {
                    $this->getActivityService()->trigger($activityId, 'doing', ['task' => $task, 'lastTime' => $data['lastTime']]);
                }
                $this->getActivityService()->trigger($activityId, 'finish', ['taskId' => $task['id']]);
                $this->getTaskService()->finishTaskResult($task['id']);
            }
        }

        $status = $activity['endTime'] < time() ? 'live_end' : 'on_live';

        return $this->createJsonResponse(['success' => true, 'status' => $status]);
    }

    public function finishConditionAction(Request $request, $activity)
    {
        return $this->render('activity/live/finish-condition.html.twig', []);
    }

    /**
     * @param $courseId
     * @param $activityId
     * @param $replayId
     * 第三方供应商的直播播放
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function customReplayEntryAction(Request $request, $courseId, $activityId, $replayId)
    {
        $user = $this->getUser();
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
        $isTeacher = false;
        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $this->getUser()->id)) {
            $isTeacher = $this->getUser()->isTeacher();
            $role = 'teacher';
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $role = 'student';
        } else {
            return $this->createMessageResponse('info', 'message_response.not_student_cannot_join_live.message');
        }

        return $this->render('live-course/entry.html.twig', [
            'courseId' => $courseId,
            'replayId' => $replayId,
            'activityId' => $activityId,
            'task' => $task,
            'isTeacher' => $isTeacher,
            'role' => $role,
        ]);
    }

    public function replayEntryAction(Request $request, $courseId, $activityId, $replayId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId);

        return $this->render('live-course/classroom.html.twig', [
            'lesson' => $activity,
            'url' => $this->generateUrl('live_activity_replay_url', [
                'courseId' => $courseId,
                'replayId' => $replayId,
                'activityId' => $activityId,
            ]),
        ]);
    }

    public function replayUrlAction(Request $request, $courseId, $activityId, $replayId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
        if (empty($task)) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        if (false === $this->getTaskService()->canLearnTask($task['id'])) {
            $this->createNewException(TaskException::CAN_NOT_DO());
        }

        $activity = $this->getActivityService()->getActivity($activityId);

        $sourceActivityId = empty($activity['copyId']) ? $activity['id'] : $activity['copyId'];

        $replay = $this->getLiveReplayService()->getReplay($replayId);
        if (empty($replay) || $replay['lessonId'] != $sourceActivityId || (bool) $replay['hidden']) {
            $this->createNewException(TaskException::LIVE_REPLAY_NOT_FOUND());
        }

        $sourceActivity = $this->getActivityService()->getActivity($sourceActivityId, true);
        $result = $this->getLiveReplayService()->entryReplay($replay['id'], $sourceActivity['ext']['liveId'], $sourceActivity['ext']['liveProvider'], $request->isSecure());

        if (!empty($result) && !empty($result['resourceNo'])) {
            $result['url'] = $this->generateUrl('es_live_room_replay_show', [
                'targetType' => LiveroomController::LIVE_COURSE_TYPE,
                'targetId' => $activity['fromCourseId'],
                'lessonId' => $activity['id'],
                'replayId' => $replay['id'],
            ]);
        }

        return $this->createJsonResponse([
            'url' => empty($result['url']) ? '' : $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null,
            'error' => isset($result['error']) ? $result['error'] : null,
        ]);
    }

    private function freshTaskLearnStat(Request $request, $activityId)
    {
        $key = 'activity.'.$activityId;
        $session = $request->getSession();
        $taskStore = $session->get($key, []);
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
        if (LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS == $activity['ext']['replayStatus']) {
            $file = $this->getUploadFileService()->getFullFile($activity['ext']['mediaId']);

            return [
                'url' => $this->generateUrl('task_live_replay_player', [
                    'activityId' => $activity['id'],
                    'courseId' => $activity['fromCourseId'],
                ]),
                'title' => $file['filename'],
            ];
        } else {
            return [];
        }
    }

    // Refactor: redesign course_lesson_replay table
    protected function _getLiveReplays($activity)
    {
        if (LiveReplayService::REPLAY_GENERATE_STATUS === $activity['ext']['replayStatus']) {
            $copyId = empty($activity['copyId']) ? $activity['id'] : $activity['copyId'];

            $replays = $this->getLiveReplayService()->findReplayByLessonId($copyId);

            $replays = array_filter($replays, function ($replay) {
                // 过滤掉被隐藏的录播回放
                return !empty($replay) && !(bool) $replay['hidden'];
            });

            $self = $this;
            $replays = array_map(function ($replay) use ($activity, $self) {
                $replay['url'] = $self->generateUrl('custom_live_activity_replay_entry', [
                    'courseId' => $activity['fromCourseId'],
                    'activityId' => $activity['id'],
                    'replayId' => $replay['id'],
                ]);

                return $replay;
            }, $replays);
        } else {
            $replays = [];
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

    protected function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
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

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }
}
