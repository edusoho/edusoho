<?php

namespace AppBundle\Controller;

use Biz\Accessor\AccessorInterface;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\LiveReplayException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LiveReplayService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Symfony\Component\HttpFoundation\Request;

class LiveroomController extends BaseController
{
    const LIVE_OPEN_COURSE_TYPE = 'open_course';
    const LIVE_COURSE_TYPE = 'course';

    public function _entryAction(Request $request, $roomId, $params = [])
    {
        $user = $request->query->all();
        $user['device'] = $this->getDevice($request);

        if ($request->isSecure()) {
            $user['protocol'] = 'https';
        }

        $systemUser = $this->getUserService()->getUser($user['id']);
        $avatar = !empty($systemUser['smallAvatar']) ? $systemUser['smallAvatar'] : '';
        $avatar = $this->getWebExtension()->getFurl($avatar, 'avatar.png');
        $user['avatar'] = $avatar;

        $biz = $this->getBiz();
        $user['hostname'] = $biz['env']['base_url'];

        if (in_array($user['role'], ['speaker', 'teacher'])) {
            $schemeAndHost = $request->getSchemeAndHttpHost();
            $user['callbackUrl'] = $this->generateCallbackUrl($schemeAndHost, $params);
        }

        $liveActivity = $this->getLiveActivityService()->getBySyncIdGTAndLiveId($roomId);
        if (!empty($liveActivity)) {
            $ticket = $this->getS2B2CFacadeService()->getS2B2CService()->getLiveEntryTicket($roomId, $user);
        } else {
            $ticket = CloudAPIFactory::create('leaf')->post("/liverooms/{$roomId}/tickets", $user);
        }

        return $this->render('liveroom/entry.html.twig', [
            'roomId' => $roomId,
            'params' => $params,
            'ticket' => $ticket,
            'liveRole' => !empty($user['role']) ? $user['role'] : 'student',
        ]);
    }

    protected function generateCallbackUrl($host, $params)
    {
        $callbackArgs = [
            'sources' => ['course', 'my', 'public'], //支持课程资料读取，公共资料读取，还有我的资料库读取
            'userId' => $this->getCurrentUser()->getId(),
        ];

        if (!empty($params['courseId'])) {
            $callbackArgs['courseId'] = $params['courseId'];
        }

        $options = [];
        if (!empty($params['startTime']) && !empty($params['endTime'])) {
            //直播前后六小时有效
            $options['exp'] = $params['endTime'] + 60 * 60 * 6;
            $options['iat'] = $params['startTime'] - 60 * 60 * 6;
        }

        $token = $this->getJWTAuth()->auth($callbackArgs, $options);

        return "{$host}/callback/ESLive?ac=callback.fetch&token={$token}";
    }

    /**
     * [playESLiveReplayAction 播放ES直播回放].
     */
    public function playESLiveReplayAction(Request $request, $targetType, $targetId, $lessonId, $replayId)
    {
        $replay = $this->getLiveReplayService()->getReplay($replayId);
        if (empty($replay)) {
            $this->createNewException(LiveReplayException::NOTFOUND_LIVE_REPLAY());
        }

        if ($this->canTakeReplay($targetType, $targetId, $lessonId, $replayId)) {
            return $this->forward('AppBundle:MaterialLib/GlobalFilePlayer:player', ['globalId' => $replay['globalId']]);
        }

        $this->createNewException(LiveReplayException::NOTFOUND_LIVE_REPLAY());
    }

    public function ticketAction(Request $request, $roomId)
    {
        $ticketNo = $request->query->get('ticket');
        $liveActivity = $this->getLiveActivityService()->getBySyncIdGTAndLiveId($roomId);
        if (!empty($liveActivity)) {
            $ticket = $this->getS2B2CFacadeService()->getS2B2CService()->consumeLiveEntryTicket($roomId, $ticketNo);
        } else {
            $ticket = CloudAPIFactory::create('leaf')->get("/liverooms/{$roomId}/tickets/{$ticketNo}");
        }

        return $this->createJsonResponse($ticket);
    }

    protected function canTakeOpenCourseReplay($courseId, $replayId)
    {
        $openCourse = $this->getOpenCourseService()->getCourse($courseId);
        $replay = $this->getLiveReplayService()->getReplay($replayId);

        return 'published' == $openCourse['status'] && $openCourse['id'] == $replay['courseId'] && $openCourse['type'] == $replay['type'];
    }

    protected function canTakeCourseReplay($courseId, $activityId, $replayId)
    {
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
        if (!$task) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }
        $access = $this->getCourseService()->canLearnTask($task['id']);

        return AccessorInterface::SUCCESS == $access['code'];
    }

    protected function canTakeReplay($targetType, $targetId, $lessonId, $replayId)
    {
        if (self::LIVE_OPEN_COURSE_TYPE === $targetType) {
            return $this->canTakeOpenCourseReplay($targetId, $replayId);
        } elseif (self::LIVE_COURSE_TYPE === $targetType) {
            return $this->canTakeCourseReplay($targetId, $lessonId, $replayId);
        }

        return false;
    }

    protected function getDevice($request)
    {
        if ($this->isMobileClient()) {
            return 'mobile';
        } else {
            return 'desktop';
        }
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
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
