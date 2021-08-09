<?php

namespace AppBundle\Controller\Callback;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\LiveReplayService;
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

    public function handleEvent($request)
    {
        $event = $request->request->get('event');
        switch ($event) {
            case 'room.started':
            case 'room.finished':
            case 'replay.generated':
                $this->getLiveReplayService()->handleReplayGenerateEvent($request->request->get('replayDatas'));
                break;
            default:
                break;
        }
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
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }
}
