<?php
namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Util\EdusohoLiveClient;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\LiveReplayService;

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

        $replay['userId']      = $user['id'];
        $replay['createdTime'] = time();

        $replay = $this->getLessonReplayDao()->create($replay);

        $this->dispatchEvent("live.replay.create", array('replay' => $replay));

        return $replay;
    }

    public function deleteReplayByLessonId($lessonId, $lessonType = 'live')
    {
        $result = $this->getLessonReplayDao()->deleteByLessonId($lessonId, $lessonType);
        $this->dispatchEvent("live.replay.delete", array('lessonId' => $lessonId));

        return $result;
    }

    public function deleteReplaysByCourseId($courseId, $lessonType = 'live')
    {
        $result = $this->getLessonReplayDao()->deleteByCourseId($courseId, $lessonType);
        $this->dispatchEvent("live.replay.delete", array('courseId' => $courseId));

        return $result;
    }

    public function updateReplay($id, $fields)
    {
        $replay = $this->getLessonReplayDao()->get($id);

        if (empty($replay)) {
            throw $this->createNotFoundException('Live replay not found');
        }

        $fields = ArrayToolkit::parts($fields, array('hidden', 'title'));

        $replay = $this->getLessonReplayDao()->update($id, $fields);

        $this->dispatchEvent("live.replay.update", array('replay' => $replay));

        return $replay;
    }

    public function updateReplayByLessonId($lessonId, $fields, $lessonType = 'live')
    {
        $fields = ArrayToolkit::parts($fields, array('hidden'));

        return $this->getLessonReplayDao()->updateByLessonId($lessonId, $fields, $lessonType);
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
        $user   = $this->getCurrentUser();

        $args = array(
            'liveId'   => $liveId,
            'replayId' => $replay['replayId'],
            'provider' => $liveProvider,
            'user'     => $user->isLogin() ? $user['email'] : '',
            'nickname' => $user->isLogin() ? $user['nickname'] : 'guest'
        );

        if ($ssl) {
            $args['protocol'] = 'https';
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
                $this->updateReplay($replay['id'], array('hidden' => 1));
            } elseif ($replay['hidden'] && in_array($replay['id'], $showReplayIds)) {
                $this->updateReplay($replay['id'], array('hidden' => 0));
            }
        }

        return true;
    }

    public function generateReplay($liveId, $courseId, $lessonId, $liveProvider, $type)
    {
        $replayList = $this->createLiveClient()->createReplayList($liveId, "录播回放", $liveProvider);

        if (isset($replayList['error']) && !empty($replayList['error'])) {
            return $replayList;
        }

        $this->deleteReplayByLessonId($lessonId, $type);

        if (!empty($replayList['data'])) {
            $replayList = json_decode($replayList['data'], true);
        }

        $replays = array();
        foreach ($replayList as $key => $replay) {
            $fields = array(
                'courseId' => $courseId,
                'lessonId' => $lessonId,
                'title'    => $replay['subject'],
                'replayId' => $replay['id'],
                'globalId' => !empty($replay['resourceNo']) ? $replay['resourceNo'] : 0,
                'type'     => $type
            );

            $replays[] = $this->addReplay($fields);
        }

        $this->dispatchEvent("live.replay.generate", array('replays' => $replays));

        return $replayList;
    }

    /**
     * only for mock
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
}
