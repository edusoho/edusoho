<?php

namespace Biz\Activity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\CurlToolkit;
use Biz\Activity\ActivityException;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\LiveActivityService;
use Biz\AppLoggerConstant;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LiveReplayService;
use Biz\File\Service\UploadFileService;
use Biz\Live\Service\LiveService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Event\Event;

class LiveActivityServiceImpl extends BaseService implements LiveActivityService
{
    private $client;

    public function getLiveActivity($id)
    {
        return $this->getLiveActivityDao()->get($id);
    }

    public function findLiveActivitiesByIds($ids)
    {
        return $this->getLiveActivityDao()->findByIds($ids);
    }

    public function findLiveActivitiesByReplayTagId($tagId)
    {
        return $this->getLiveActivityDao()->findLiveActivitiesByReplayTagId($tagId);
    }

    public function findActivityByLiveActivityId($id)
    {
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            $this->createNewException(LiveActivityException::NOTFOUND_LIVE());
        }
        $conditions = [
            'mediaId' => $liveActivity['id'],
            'mediaType' => 'live',
        ];
        $activities = $this->getActivityDao()->search($conditions, ['endTime' => 'DESC'], 0, 1);
        if (empty($activities)) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }
        if (!isset($activities[0])) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }
        $activity = array_merge($activities[0], $liveActivity);

        return $activity;
    }

    public function createLiveActivity($activity, $ignoreValidation = false)
    {
        if (!$ignoreValidation && (empty($activity['startTime'])
                || $activity['startTime'] <= time()
                || empty($activity['length'])
                || $activity['length'] <= 0)
        ) {
            $this->createNewException(LiveActivityException::LIVE_TIME_INVALID());
        }

        //创建直播室
        if (empty($activity['startTime'])
            || $activity['startTime'] <= time()
        ) {
            //此时不创建直播教室
            $live = [
                'id' => 0,
                'provider' => 0,
            ];
        } else {
            $live = $this->createLiveroom($activity);

            if (empty($live)) {
                $this->createNewException(LiveActivityException::CREATE_LIVEROOM_FAILED());
            }

            if (isset($live['error'])) {
                $error = '帐号已过期' == $live['error'] ? '直播服务已过期' : $live['error'];
                throw $this->createServiceException($error, 500);
            }
            $this->dispatchEvent('live.activity.create', new Event($live['id'], ['activity' => $activity, 'live' => $live]));
        }

        if (!empty($activity['roomType']) && !$this->isRoomType($activity['roomType'])) {
            $this->createNewException(LiveActivityException::ROOMTYPE_INVALID());
        }

        $liveActivity = [
            'liveId' => $live['id'],
            'liveProvider' => $live['provider'],
            'roomType' => empty($activity['roomType']) ? EdusohoLiveClient::LIVE_ROOM_LARGE : $activity['roomType'],
            'roomCreated' => $live['id'] > 0 ? 1 : 0,
            'fileIds' => $activity['fileIds'],
            'liveStartTime' => empty($activity['startTime']) ? 0 : $activity['startTime'],
            'liveEndTime' => $activity['startTime'] + $activity['length'] * 60,
            'anchorId' => $this->getCurrentUser()->getId(),
            'coursewareIds' => empty($live['coursewareIds']) ? [] : $live['coursewareIds'],
        ];
        if (EdusohoLiveClient::SELF_ES_LIVE_PROVIDER == $live['provider']) {
            $liveActivity['roomId'] = $live['roomId'] ?? 0;
        }

        return $this->getLiveActivityDao()->create($liveActivity);
    }

    public function updateLiveActivity($id, $fields, $activity)
    {
        $preLiveActivity = $liveActivity = $this->getLiveActivityDao()->get($id);

        if (empty($liveActivity)) {
            return [];
        }
        $fields = array_merge($activity, $fields);
        if (!$liveActivity['roomCreated']) {
            if ($fields['startTime'] > time()) {
                $live = $this->createLiveroom($fields);
                $liveActivity['liveId'] = $live['id'];
                $liveActivity['liveProvider'] = $live['provider'];
                $liveActivity['roomCreated'] = 1;
                $liveActivity['roomType'] = empty($fields['roomType']) ? EdusohoLiveClient::LIVE_ROOM_LARGE : $fields['roomType'];
                $liveActivity['coursewareIds'] = empty($live['coursewareIds']) ? [] : $live['coursewareIds'];

                $liveActivity = $this->getLiveActivityDao()->update($id, $liveActivity);
            }
        } elseif ($fields['endTime'] > time()) {
            //直播还未结束的情况下才更新直播房间信息
            $liveParams = [
                'liveId' => $liveActivity['liveId'],
                'summary' => empty($fields['remark']) ? '' : $fields['remark'],
                'title' => $fields['title'],
            ];
            //直播开始后不更新开始时间和直播时长
            if ($fields['startTime'] > time()) {
                $liveParams['startTime'] = $fields['startTime'];
                $liveParams['endTime'] = (string) ($fields['startTime'] + $fields['length'] * 60);
            }

            if (!empty($fields['roomType']) && $this->canUpdateRoomType($activity['startTime'])) {
                $liveParams['roomType'] = $fields['roomType'];
            }

            $this->getEdusohoLiveClient()->updateLive($liveParams);
            if (EdusohoLiveClient::SELF_ES_LIVE_PROVIDER == $liveActivity['liveProvider']) {
                if (EdusohoLiveClient::LIVE_ROOM_PSEUDO == $fields['roomType']) {
                    if (!empty(array_diff($fields['fileIds'], $liveActivity['fileIds']))) {
                        $client = new EdusohoLiveClient();
                        $result = $client->updatePseudoLiveVideo($liveActivity['liveId'], $this->getPseudoLiveVideoUrl($fields));
                        $this->getLogService()->info('es_live', 'update', '修改智能直播视屏', $result);
                    }
                } else {
                    $fileIds = empty($fields['fileIds']) ? [-1] : $fields['fileIds'];
                    $coursewareIds = $this->createLiveroomCoursewares($liveActivity['liveId'], $fileIds);
                    $this->getLiveActivityDao()->update($id, ['coursewareIds' => $coursewareIds]);
                }
            }
        }

        $live = ArrayToolkit::parts($fields, ['replayStatus', 'fileId', 'roomType', 'fileIds', 'replayPublic']);

        if (!empty($live['fileId'])) {
            $live['mediaId'] = $live['fileId'];
            $live['replayStatus'] = LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS;
        }

        unset($live['fileId']);

        if (!empty($live)) {
            $liveActivity = $this->getLiveActivityDao()->update($id, $live);
        }

        $this->dispatchEvent('live.activity.update', new Event($liveActivity, ['fields' => $live, 'liveId' => $liveActivity['liveId'], 'activity' => $activity, 'updateActivity' => $fields]));

        return [$liveActivity, $fields];
    }

    public function updateLiveStatus($id, $status)
    {
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            return;
        }

        if (!in_array($status, [EdusohoLiveClient::LIVE_STATUS_LIVING, EdusohoLiveClient::LIVE_STATUS_CLOSED, EdusohoLiveClient::LIVE_STATUS_PAUSE])) {
            $this->createNewException(LiveActivityException::LIVE_STATUS_INVALID());
        }

        $update = $this->getLiveActivityDao()->update($liveActivity['id'], ['progressStatus' => $status]);
        $this->getLogService()->info(AppLoggerConstant::LIVE, 'update_live_status', "修改直播进行状态，由‘{$liveActivity['progressStatus']}’改为‘{$status}’", ['preLiveActivity' => $liveActivity, 'newLiveActivity' => $update]);

        return $update;
    }

    public function startLive($liveId, $startTime)
    {
        $liveActivity = $this->getLiveActivityDao()->getByLiveId($liveId);
        if (empty($liveActivity)) {
            return;
        }
        if(empty($startTime)) {
            $startTime = $liveActivity['liveStartTime'];
        }
        $activities = $this->getActivityDao()->findActivitiesByMediaIdsAndMediaType([$liveActivity['id']], 'live');
        $update = ['progressStatus' => EdusohoLiveClient::LIVE_STATUS_LIVING, 'liveStartTime' => $startTime];
        foreach ($activities as $activity) {
            if (0 == $activity['copyId']) {
                $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
                if (!empty($course['teacherIds'])) {
                    $update['anchorId'] = $course['teacherIds'][0];
                }
            }
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
            $this->getTaskDao()->update($task['id'], ['startTime' => $startTime]);
            $this->getActivityDao()->update($activity['id'], ['startTime' => $startTime]);
        }
        $newLiveActivity = $this->getLiveActivityDao()->update($liveActivity['id'], $update);
        $this->getLogService()->info(AppLoggerConstant::LIVE, 'update_live_status', '直播开始', ['preLiveActivity' => $liveActivity, 'newLiveActivity' => $newLiveActivity]);
        $this->dispatchEvent('live.status.start', new Event($liveActivity['liveId']));
    }

    public function closeLive($liveId, $closeTime)
    {
        $liveActivity = $this->getLiveActivityDao()->getByLiveId($liveId);
        if (empty($liveActivity) || (!empty($liveActivity['liveStartTime']) && time() < $liveActivity['liveStartTime']) || EdusohoLiveClient::LIVE_STATUS_CLOSED == $liveActivity['progressStatus']) {
            return;
        }
        if(empty($closeTime)) {
            $closeTime = $liveActivity['liveEndTime'];
        }
        $activities = $this->getActivityDao()->findActivitiesByMediaIdsAndMediaType([$liveActivity['id']], 'live');
        $this->getLiveActivityDao()->update($liveActivity['id'], ['progressStatus' => EdusohoLiveClient::LIVE_STATUS_CLOSED, 'liveEndTime' => $closeTime]);
        foreach ($activities as $activity) {
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
            $this->getTaskDao()->update($task['id'], ['endTime' => $closeTime]);
            $this->getActivityDao()->update($activity['id'], ['endTime' => $closeTime]);
        }
        $this->getLogService()->info(AppLoggerConstant::LIVE, 'update_live_status', '直播结束', []);
        $this->dispatchEvent('live.status.close', new Event($liveActivity['liveId']));
    }

    public function deleteLiveActivity($id)
    {
        //删除直播室
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            return;
        }

        $this->getLiveActivityDao()->delete($id);
        if (!empty($liveActivity['liveId'])) {
            $this->getEdusohoLiveClient()->deleteLive($liveActivity['liveId']);
            $this->dispatchEvent('live.activity.delete', new Event($liveActivity['liveId']));
        }
    }

    public function search($conditions, $orderbys, $start, $limit)
    {
        return $this->getLiveActivityDao()->search($conditions, $orderbys, $start, $limit);
    }

    public function count($conditions)
    {
        return $this->getLiveActivityDao()->count($conditions);
    }

    public function updateLiveActivityWithoutEvent($liveActivityId, $fields)
    {
        return $this->getLiveActivityDao()->update($liveActivityId, $fields);
    }

    public function shareLiveReplay($liveActivityId)
    {
        return $this->getLiveActivityDao()->update($liveActivityId, ['replayPublic' => 1]);
    }

    public function unShareLiveReplay($liveActivityId)
    {
        return $this->getLiveActivityDao()->update($liveActivityId, ['replayPublic' => 0]);
    }

    public function updateLiveReplayTags($liveActivityId, $tagIds)
    {
        return $this->getLiveActivityDao()->update($liveActivityId, ['replayTagIds' => $tagIds]);
    }

    public function removeLiveReplay($liveActivityId)
    {
        $liveActivity = $this->getLiveActivityDao()->update($liveActivityId, ['replayPublic' => 0, 'replayStatus' => 'ungenerated']);
        $activity = $this->getActivityDao()->getByMediaIdAndMediaType($liveActivity['id'], 'live');
        if (empty($activity)) {
            return true;
        }
        $this->getLiveReplayService()->deleteReplayByLessonId($activity['id']);

        return true;
    }

    public function getByLiveId($liveId)
    {
        return $this->getLiveActivityDao()->getByLiveId($liveId);
    }

    public function findLiveActivitiesByLiveIds($liveIds)
    {
        return $this->getLiveActivityDao()->findByLiveIds($liveIds);
    }

    /**
     * 是否可以更新 roomType， 直播开始前10分钟内和直播结束后不可修改
     *
     * @param [type] $liveStartTime 直播开始时间
     *
     * @return bool
     */
    public function canUpdateRoomType($liveStartTime)
    {
        $timeDiff = $liveStartTime - time();
        $disableSeconds = 3600 * 2;

        if ($timeDiff < 0 || ($timeDiff > 0 && $timeDiff <= $disableSeconds)) {
            return 0;
        }

        return 1;
    }

    public function getBySyncIdGTAndLiveId($liveId)
    {
        return $this->getLiveActivityDao()->getBySyncIdGTAndLiveId($liveId);
    }

    protected function isRoomType($liveRoomType)
    {
        return in_array($liveRoomType, [EdusohoLiveClient::LIVE_ROOM_LARGE, EdusohoLiveClient::LIVE_ROOM_SMALL, EdusohoLiveClient::LIVE_ROOM_PSEUDO]);
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->createDao('Activity:LiveActivityDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getEdusohoLiveClient()
    {
        if (empty($this->client)) {
            $this->client = new EdusohoLiveClient();
        }

        return $this->client;
    }

    /**
     * @param  $activity
     *
     * @throws \Biz\User\UserException
     * @throws \Biz\Activity\LiveActivityException
     * @throws \Exception
     *
     * @return array
     */
    public function createLiveroom($activity)
    {
        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $speakerId = empty($course['teacherIds']) ? $activity['fromUserId'] : $course['teacherIds'][0];
        $speaker = $this->getUserService()->getUser($speakerId);
        if (empty($speaker)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (!empty($activity['roomType']) && !$this->isRoomType($activity['roomType'])) {
            $this->createNewException(LiveActivityException::ROOMTYPE_INVALID());
        }

        $liveLogo = $this->getSettingService()->get('course');
        $liveLogoUrl = '';
        $baseUrl = $this->biz['env']['base_url'];
        if (!empty($liveLogo) && array_key_exists('live_logo', $liveLogo) && !empty($liveLogo['live_logo'])) {
            $liveLogoUrl = $baseUrl.'/'.$liveLogo['live_logo'];
        }

        $remark = empty($activity['remark']) ? '' : strip_tags($activity['remark'], '<img>');
        $remark = html_entity_decode($remark);
        $liveData = [
            'summary' => $remark,
            'title' => $activity['title'],
            'speaker' => $speaker['nickname'],
            'startTime' => $activity['startTime'].'',
            'endTime' => ($activity['startTime'] + $activity['length'] * 60).'',
            'authUrl' => $baseUrl.'/live/auth',
            'jumpUrl' => $baseUrl.'/live/jump?id='.$activity['fromCourseId'],
            'liveLogoUrl' => $liveLogoUrl,
            'roomType' => empty($activity['roomType']) ? EdusohoLiveClient::LIVE_ROOM_LARGE : $activity['roomType'],
        ];
        if (EdusohoLiveClient::LIVE_ROOM_PSEUDO == $activity['roomType']) {
            $liveData['mode'] = 'pseudo';
            $liveData['roomType'] = EdusohoLiveClient::LIVE_ROOM_LARGE;
            $liveData['pseudoVideoUrl'] = $this->getPseudoLiveVideoUrl($activity);
        }
        $liveAccount = $this->getEdusohoLiveClient()->getLiveAccount();
        $liveData['teacherId'] = $this->getLiveService()->getLiveProviderTeacherId($speakerId, $liveAccount['provider']);

        $live = $this->getEdusohoLiveClient()->createLive($liveData);

        if (EdusohoLiveClient::LIVE_ROOM_PSEUDO == $activity['roomType']) {
            return $live;
        }
        // 给直播间（自研）添加课件
        if (isset($live['provider']) && EdusohoLiveClient::SELF_ES_LIVE_PROVIDER == $live['provider'] && $activity['fileIds']) {
            $live['coursewareIds'] = $this->createLiveroomCoursewares($live['id'], $activity['fileIds']);
        }

        return $live;
    }

    protected function getPseudoLiveVideoUrl($activity)
    {
        if (empty($activity['fileIds'])) {
            $this->createNewException(LiveActivityException::CREATE_LIVEROOM_FAILED());
        }
        $file = $this->getUploadFileService()->getFile($activity['fileIds'][0]);

        $result = $this->biz['ESCloudSdk.play']->makePlayUrl(
            $file['globalId'],
            600,
            ['native' => 1]
        );
        $result = CurlToolkit::request('get', $this->getSchema().$result);
        if (empty($result['type']) || 'video' != $result['type'] || empty($result['args']['playlist'])) {
            $this->createNewException(LiveActivityException::CREATE_LIVEROOM_FAILED());
        }
        $playlist = $result['args']['playlist'];

        array_multisort(array_column($playlist, 'bandwidth'), SORT_DESC, $playlist);
        $playlist = array_values($playlist);
        $url = str_replace('https:', '', $playlist[0]['url']);

        $url = str_replace('http:', '', $url);

        return str_replace('ssl=0', 'ssl=1', $url);
    }

    protected function getSchema()
    {
        $https = empty($_SERVER['HTTPS']) ? '' : $_SERVER['HTTPS'];
        if (!empty($https) && 'off' !== strtolower($https)) {
            return 'https:';
        }

        return 'http:';
    }

    /**
     * @param  $liveId
     * @param  $fileIds
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createLiveroomCoursewares($liveId, $fileIds)
    {
        $liveActivity = $this->getByLiveId($liveId);
        $liveActivity['coursewareIds'] = empty($liveActivity['coursewareIds']) ? [] : $liveActivity['coursewareIds'];
        $files = $this->getUploadFileService()->findFilesByIds($fileIds);
        $storageSetting = $this->getSettingService()->get('storage', []);

        // 直播课件上传文件信息保存
        $liveCoursewares = [];
        // 直播课件已上传文件信息
        $coursewareIds = [];

        foreach ($files as $file) {
            $fileId = $file['id'];
            if (!empty($liveActivity['coursewareIds'][$fileId])) {
                $coursewareIds[$fileId] = $liveActivity['coursewareIds'][$fileId];
                unset($liveActivity['coursewareIds'][$fileId]);
                continue;
            }

            $liveCoursewares[$fileId] = $this->getEdusohoLiveClient()->createLiveCourseware([
                'liveId' => $liveId,
                'resources' => [
                    'name' => $file['filename'],
                    'fromResNo' => $file['globalId'],
                    'copyToken' => $storageSetting['cloud_access_key'].':'.md5($file['globalId']."\n".$storageSetting['cloud_secret_key']),
                ],
            ]);
        }

        foreach ($liveCoursewares as $key => $liveCourseware) {
            if (isset($liveCourseware['id'])) {
                $coursewareIds[$key] = $liveCourseware['id'];
            }
        }

        // 对比新增/删除直播课件信息
        $newCoursewareIds = array_diff($coursewareIds, $liveActivity['coursewareIds']);
        $deleteCoursewareIds = array_diff($liveActivity['coursewareIds'], $coursewareIds);

        $this->deleteLiveroomCoursewares($liveId, $deleteCoursewareIds);

        return $newCoursewareIds;
    }

    private function deleteLiveroomCoursewares($liveId, $coursewareIds)
    {
        foreach ($coursewareIds as $coursewareId) {
            $this->getEdusohoLiveClient()->deleteLiveCourseware([
                'liveId' => $liveId,
                'coursewareId' => $coursewareId,
            ]);
        }
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }

    /**
     * @return LiveService
     */
    protected function getLiveService()
    {
        return $this->createService('Live:LiveService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
