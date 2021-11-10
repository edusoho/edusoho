<?php

namespace AppBundle\Controller\Callback;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\LiveReplayService;
use Biz\Live\Service\LiveService;
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

    public function handleEvent(Request $request)
    {
        try {
            $eventData = $request->request->all();
            $status = isset($eventData['event']) ? $eventData['event'] : 'null';
            $this->getLogService()->info('live_callback', 'live_callback', '直播状态回调'.json_encode($eventData));
            switch ($status) {
                case 'room.started':
                    $this->getLiveActivityService()->startLive($eventData['id'], $eventData['startTime']);
                    break;
                case 'room.finished':
                    $confirmStatus = $this->getLiveService()->confirmLiveStatus($eventData['id']);
                    if (isset($confirmStatus[$eventData['id']]['status']) && 'finished' === $confirmStatus['status']) {
                        $this->getLiveActivityService()->closeLive($eventData['id'], $eventData['endTime']);
                    }
                    break;
                case 'replay.finished':
                    $this->getLiveReplayService()->handleReplayGenerateEvent($eventData['id'], $eventData['replayDatas']);
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @return LiveService
     */
    protected function getLiveService()
    {
        return $this->createService('Live:LiveService');
    }

    /**
     * @return LiveActivityService
     */
    private function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }
}
