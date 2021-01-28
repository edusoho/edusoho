<?php

namespace Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\LiveActivityService;
use Biz\BaseService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Course\LiveReplayException;
use Biz\Course\Service\LiveReplayService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\LogService;
use Biz\Util\EdusohoLiveClient;

// Refactor: 该类不应该在Course模块，应该在和LiveActivity放一块，或者另启一个模块LiveRoom
class LiveReplayServiceImpl extends BaseService implements LiveReplayService
{
    private $liveClient = null;

    public function getReplay($id)
    {
        return $this->getLessonReplayDao()->get($id);
    }

    public function findReplayByLessonId($lessonId, $lessonType = 'live')
    {
        return $this->getLessonReplayDao()->findByLessonId($lessonId, $lessonType);
    }

    public function addReplay($replay)
    {
        $user = $this->getCurrentUser();

        $replay['userId'] = $user['id'];
        $replay['createdTime'] = time();

        $replay = $this->getLessonReplayDao()->create($replay);

        $this->dispatchEvent('live.replay.create', ['replay' => $replay]);

        return $replay;
    }

    public function deleteReplayByLessonId($lessonId, $lessonType = 'live')
    {
        $result = $this->getLessonReplayDao()->deleteByLessonId($lessonId, $lessonType);
        $this->dispatchEvent('live.replay.delete', ['lessonId' => $lessonId]);

        return $result;
    }

    public function deleteReplaysByCourseId($courseId, $lessonType = 'live')
    {
        $result = $this->getLessonReplayDao()->deleteByCourseId($courseId, $lessonType);
        $this->dispatchEvent('live.replay.delete', ['courseId' => $courseId]);

        return $result;
    }

    public function updateReplay($id, $fields)
    {
        $replay = $this->getLessonReplayDao()->get($id);

        if (empty($replay)) {
            $this->createNewException(LiveReplayException::NOTFOUND_LIVE_REPLAY());
        }

        $fields = ArrayToolkit::parts($fields, ['hidden', 'title']);

        $replay = $this->getLessonReplayDao()->update($id, $fields);

        $this->dispatchEvent('live.replay.update', ['replay' => $replay]);

        return $replay;
    }

    public function updateReplayByLessonId($lessonId, $fields, $lessonType = 'live')
    {
        $fields = ArrayToolkit::parts($fields, ['hidden']);

        return $this->getLessonReplayDao()->updateByLessonId($lessonId, $lessonType, $fields);
    }

    public function searchCount($conditions)
    {
        return $this->getLessonReplayDao()->count($conditions);
    }

    public function searchReplays($conditions, $orderBy, $start, $limit)
    {
        return $this->getLessonReplayDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findReplaysByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        return $this->getLessonReplayDao()->findByCourseIdAndLessonId($courseId, $lessonId, $lessonType);
    }

    public function entryReplay($replayId, $liveId, $liveProvider, $ssl = false)
    {
        $replay = $this->getReplay($replayId);
        $user = $this->getCurrentUser();

        $args = [
            'liveId' => $liveId,
            'replayId' => $replay['replayId'],
            'provider' => $liveProvider,
            'user' => $user->isLogin() ? $user['email'] : '',
            'nickname' => $user->isLogin() ? $user['nickname'] : 'guest',
        ];

        //用来计算当前直播用户数量
        if ($user->isLogin()) {
            $args['userId'] = $user['id'];
        }

        if ($ssl) {
            $args['protocol'] = 'https';
        }

        $liveActivity = $this->getLiveActivityService()->getBySyncIdGTAndLiveId($liveId);
        if (!empty($liveActivity)) {
            return $this->getS2B2CFacadeService()->getS2B2CService()->entryLiveReplay($args);
        }

        return $this->createLiveClient()->entryReplay($args);
    }

    public function updateReplayShow($showReplayIds, $lessonId)
    {
        $replayLessons = $this->findReplayByLessonId($lessonId);

        if (!$replayLessons) {
            return false;
        }

        foreach ($replayLessons as $replay) {
            if (empty($showReplayIds) || (!$replay['hidden'] && !in_array($replay['id'], $showReplayIds))) {
                $this->updateReplay($replay['id'], ['hidden' => 1]);
            } elseif ($replay['hidden'] && in_array($replay['id'], $showReplayIds)) {
                $this->updateReplay($replay['id'], ['hidden' => 0]);
            }
        }

        return true;
    }

    public function generateReplay($liveId, $courseId, $lessonId, $liveProvider, $type)
    {
        try {
            $liveActivity = $this->getLiveActivityService()->getBySyncIdGTAndLiveId($liveId);
            if (!empty($liveActivity)) {
                $replayList = $this->getS2B2CFacadeService()->getS2B2CService()->createLiveReplayList($liveId);
            } else {
                $replayList = $this->createLiveClient()->createReplayList($liveId, '录播回放', $liveProvider);
            }
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return [];
        }

        if (isset($replayList['error']) && !empty($replayList['error'])) {
            throw $this->createServiceException($replayList['error'], 500);
        }

        $this->deleteReplayByLessonId($lessonId, $type);

        if (!empty($replayList['data'])) {
            $replayList = json_decode($replayList['data'], true);
        }

        $replays = [];
        foreach ($replayList as $replay) {
            $fields = [
                'courseId' => $courseId,
                'lessonId' => $lessonId,
                'title' => $replay['subject'],
                'replayId' => $replay['id'],
                'globalId' => empty($replay['resourceNo']) ? '' : $replay['resourceNo'],
                'type' => $type,
            ];

            $replays[] = $this->addReplay($fields);
        }

        $this->dispatchEvent('live.replay.generate', $replays);

        return $replayList;
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

    protected function getLessonReplayDao()
    {
        return $this->createDao('Course:CourseLessonReplayDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
