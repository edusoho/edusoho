<?php

namespace AppBundle\Twig;

use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LiveReplayService;
use Biz\File\Service\UploadFileService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
        return [
            new \Twig_SimpleFunction('live_can_record', [$this, 'canRecord']),
            new \Twig_SimpleFunction('is_live_finished', [$this, 'isLiveFinished']),
            new \Twig_SimpleFunction('get_live_room_type', [$this, 'getLiveRoomType']),
            new \Twig_SimpleFunction('get_live_account', [$this, 'getLiveAccount']),
            new \Twig_SimpleFunction('get_live_replays', [$this, 'getLiveReplays']),
            new \Twig_SimpleFunction('get_live_activity_replays', [$this, 'getLiveActivityReplays']),
            new \Twig_SimpleFunction('fresh_task_learn_stat', [$this, 'freshTaskLearnStat']),
        ];
    }

    public function freshTaskLearnStat(Request $request, $activityId)
    {
        $key = 'activity.'.$activityId;
        $session = $request->getSession();
        $taskStore = $session->get($key, []);
        $taskStore['start'] = time();
        $taskStore['lastTriggerTime'] = 0;

        $session->set($key, $taskStore);

        return true;
    }

    public function getLiveReplays($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);

        if (LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS == $activity['ext']['replayStatus']) {
            return [$this->_getLiveVideoReplay($activity)];
        } else {
            return $this->_getLiveReplays($activity);
        }
    }

    public function getLiveActivityReplays($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);
        $liveActivity = $this->getActivityService()->getActivity($activity['ext']['origin_lesson_id'], true);

        return $this->_getLiveActivityReplays($activity, $liveActivity);
    }

    protected function _getLiveActivityReplays($activity, $liveActivity)
    {
        $originActivity = $this->getOriginActivity($liveActivity);
        $copyId = empty($originActivity['copyId']) ? $originActivity['id'] : $originActivity['copyId'];

        $replays = $this->getLiveReplayService()->findReplayByLessonId($copyId);

        $replays = array_filter($replays, function ($replay) {
            // 过滤掉被隐藏的录播回放
            return !empty($replay) && !(bool) $replay['hidden'];
        });

        $self = $this;
        $replays = array_map(function ($replay) use ($activity, $self) {
            $replay['url'] = $self->generateUrl('course_live_activity_replay_entry', [
                'courseId' => $activity['fromCourseId'],
                'activityId' => $activity['id'],
                'replayId' => $replay['id'],
            ]);

            return $replay;
        }, $replays);

        return $replays;
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

    protected function _getLiveReplays($activity)
    {
        if (LiveReplayService::REPLAY_GENERATE_STATUS === $activity['ext']['replayStatus']) {
            $originActivity = $this->getOriginActivity($activity);
            $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
            $lessonId = empty($originActivity['copyId']) ? $originActivity['id'] : $originActivity['copyId'];
            if (!empty($course['parentId']) && empty($originActivity['copyId'])) {
                $parentActivity = $this->getActivityService()->getByMediaIdAndMediaTypeAndCourseId($originActivity['mediaId'], 'live', $course['parentId']);
                $lessonId = empty($parentActivity) ? $lessonId : $parentActivity['id'];
            }
            $lessonId = empty($lessonId) && !empty($activity['copyId']) ? $activity['copyId'] : $lessonId;

            $replays = $this->getLiveReplayService()->findReplayByLessonId($lessonId);

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

    protected function getOriginActivity($activity)
    {
        if (empty($activity['copyId'])) {
            return $activity;
        }
        $copyActivity = $this->getActivityService()->getActivity($activity['copyId']);

        return $this->getOriginActivity($copyActivity);
    }

    public function canRecord($liveId, $syncId = 0)
    {
        try {
            if ($syncId > 0) {
                return (bool) $this->getS2B2CFacadeService()->getS2B2CService()->isLiveAvailableRecord($liveId);
            }
            $client = new EdusohoLiveClient();

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
            return [];
        }

        $default = [
            'large' => 'course.live_activity.large_room_type',
            'small' => 'course.live_activity.small_room_type',
        ];

        $roomTypes = $liveAccount['roomType'];
        if (empty($roomTypes)) {
            return [];
        }

        if (count($roomTypes) >= 2) {
            return $default;
        } else {
            return [$roomTypes[0] => $default[$roomTypes[0]]];
        }
    }

    public function getLiveAccount()
    {
        $liveAccount = $this->getSettingService()->get('developer_live_account', []);
        $liveAccount = empty($liveAccount) || empty($liveAccount['settingRequestTime']) || time() - $liveAccount['settingRequestTime'] > 300 ? $this->getEdusohoLiveAccount() : $liveAccount;
        $liveAccount['isExpired'] = !empty($liveAccount['expire']) && $liveAccount['expire'] < time() ? 1 : 0;

        return $liveAccount;
    }

    protected function getEdusohoLiveAccount()
    {
        $client = new EdusohoLiveClient();
        try {
            $liveAccount = $client->getLiveAccount();
            $liveAccount['settingRequestTime'] = time();
            $this->getSettingService()->set('developer_live_account', $liveAccount);
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return ['error' => $cloudAPIIOException->getMessage()];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'live';
    }

    public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @return ActivityService
     */
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->biz->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
