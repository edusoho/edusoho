<?php

namespace AppBundle\Controller\Callback;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\LiveReplayService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Constant\LogModule;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LiveController extends BaseController
{
    public function handleAction(Request $request)
    {
        if ('GET' == $request->getMethod()) {
            return $this->createJsonResponse(['success' => false, 'message' => 'Only allow post requests']);
        }
        $authorization = $request->headers->get('authorization', '');
        $key = trim(str_replace('Secret', '', $authorization));
        $storage = $this->setting('storage', []);
        $data = JWT::decode($key, $storage['cloud_secret_key'], ['HS256']);
        if (empty($data)) {
            return $this->createJsonResponse(['success' => false, 'message' => '请求参数错误']);
        }
        $this->handleEvent($request);

        return new JsonResponse(['success' => true]);
    }

    private function handleEvent(Request $request)
    {
        try {
            $eventData = $request->request->all();
            $event = $eventData['event'] ?? 'null';
            $this->getLogService()->info(LogModule::LIVE, 'live_callback', '直播状态回调'.json_encode($eventData));
            switch ($event) {
                case 'room.started':
                    $this->getLiveActivityService()->startLive($eventData['id'], $eventData['startTime']);
                    $this->getOpenCourseService()->startLive($eventData['id'], $eventData['startTime']);
                    break;
                case 'room.finished':
                    $this->getLiveActivityService()->closeLive($eventData['id'], $eventData['endTime']);
                    $this->getOpenCourseService()->closeLive($eventData['id'], $eventData['endTime']);
                    break;
                case 'replay.finished':
                    $this->getLiveReplayService()->handleReplayGenerateEvent($eventData['id'], $eventData['replayDatas']);
                    $this->getLiveReplayService()->handleReplayGenerateEventForOpenCourse($eventData['id'], $eventData['replayDatas']);
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @return LiveActivityService
     */
    private function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
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
}
