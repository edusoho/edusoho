<?php

namespace AppBundle\Controller\Callback;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\LiveReplayService;
use Biz\Util\EdusohoLiveClient;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LiveController extends BaseController
{
    public function handleAction(Request $request)
    {
        $this->validToken($request);
        $this->handleEvent($request);

        return new JsonResponse(['success' => true]);
    }

    public function handleEvent(Request $request)
    {
        $event = $request->request->get('event');
        $liveId = $request->request->get('id');

        $method = '';
        switch ($event) {
            case 'room.started':
                $method = 'startEvent';
                break;
            case 'room.finished':
                $method = 'closeEvent';
                break;
            case 'replay.finished':
                $method = 'replayEvent';
                break;
            default:
                break;
        }

        if ($method) {
            $this->$method($request, $liveId);
        }
    }

    protected function startEvent(Request $request, $liveId)
    {
        $startTime = $request->query->get('startTime', time());
        $this->getLiveActivityService()->startLive($liveId, $startTime);
    }

    protected function closeEvent(Request $request, $liveId)
    {
        try {
            $confirmStatus = (new EdusohoLiveClient())->checkLiveStatus([$liveId]);
            if (isset($confirmStatus[$liveId]['status']) && 'closed' === $confirmStatus[$liveId]['status']) {
                $closeTime = $request->query->get('closeTime', time());
                $this->getLiveActivityService()->closeLive($liveId, $closeTime);
            }
        } catch (\Exception $e) {
            throw $e;

            return;
        }
    }

    protected function replayEvent(Request $request, $liveId)
    {
        $this->getLiveReplayService()->handleReplayGenerateEvent($request->request->get('replayDatas'));
    }

    protected function validToken($request)
    {
        $token = $request->headers->get('Authorization');
        $token = explode(' ', $token);
        $payload = JWT::decode($token[1], $this->getKey(), ['HS256']);
        if (!$payload) {
            throw new BadRequestHttpException('Token Error');
        }
    }

    protected function getKey()
    {
        $setting = $this->setting('storage', []);
        $secretKey = !empty($setting['cloud_secret_key']) ? $setting['cloud_secret_key'] : '';

        return $secretKey;
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
