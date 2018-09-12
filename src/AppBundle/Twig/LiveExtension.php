<?php

namespace AppBundle\Twig;

use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Biz\Course\Service\LiveReplayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LiveExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('live_can_record', array($this, 'canRecord')),
            new \Twig_SimpleFunction('is_live_finished', array($this, 'isLiveFinished')),
            new \Twig_SimpleFunction('get_live_room_type', array($this, 'getLiveRoomType')),
            new \Twig_SimpleFunction('get_live_account', array($this, 'getLiveAccount')),
            new \Twig_SimpleFunction('get_live_replays', array($this, 'getLiveReplays')),
            new \Twig_SimpleFunction('fresh_task_learn_stat', array($this, 'freshTaskLearnStat')),
        );
    }

    public function freshTaskLearnStat(Request $request, $activityId)
    {
        $key = 'activity.'.$activityId;
        $session = $request->getSession();
        $taskStore = $session->get($key, array());
        $taskStore['start'] = time();
        $taskStore['lastTriggerTime'] = 0;

        $session->set($key, $taskStore);

        return true;
    }

    public function getLiveReplays($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);

        if (LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS == $activity['ext']['replayStatus']) {
            return array($this->_getLiveVideoReplay($activity));
        } else {
            return $this->_getLiveReplays($activity);
        }
    }

    protected function _getLiveVideoReplay($activity, $ssl = false)
    {
        if (LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS == $activity['ext']['replayStatus']) {
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

    public function canRecord($liveId)
    {
        $client = new EdusohoLiveClient();

        try {
            return (bool) $client->isAvailableRecord($liveId);
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return false;
        }
    }

    public function isLiveFinished($mediaId, $type)
    {
        if ('openCourse' == $type) {
            return $this->getLiveCourseService()->isLiveFinished($mediaId);
        } else {
            return $this->getActivityService()->isLiveFinished($mediaId);
        }
    }

    public function getLiveRoomType()
    {
        $liveAccount = $this->getEdusohoLiveAccount();
        if (isset($liveAccount['error'])) {
            return array();
        }

        $default = array(
            'large' => 'course.live_activity.large_room_type',
            'small' => 'course.live_activity.small_room_type',
        );

        $roomTypes = $liveAccount['roomType'];
        if (empty($roomTypes)) {
            return array();
        }

        if (count($roomTypes) >= 2) {
            return $default;
        } else {
            return array($roomTypes[0] => $default[$roomTypes[0]]);
        }
    }

    public function getLiveAccount()
    {
        $liveAccount = $this->getEdusohoLiveAccount();
        $liveAccount['isExpired'] = !empty($liveAccount['expire']) && $liveAccount['expire'] < time() ? 1 : 0;

        return $liveAccount;
    }

    protected function getEdusohoLiveAccount()
    {
        $client = new EdusohoLiveClient();
        try {
            return $client->getLiveAccount();
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return array('error' => $cloudAPIIOException->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'live';
    }

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getLiveReplayService()
    {
        return $this->biz->service('Course:LiveReplayService');
    }

    protected function getLiveCourseService()
    {
        return $this->biz->service('OpenCourse:LiveCourseService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
