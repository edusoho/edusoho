<?php

namespace AppBundle\Controller\Callback;

use AppBundle\Common\JWTAuth;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LiveController extends BaseController
{
    public function handle(Request $request)
    {
        $this->validToken($request);
        $event = $request->request->get('event');

        return new JsonResponse(['success' => true]);
    }

    protected function validToken($request)
    {
        $token = $request->headers->get('Authorization');
        $result = $this->getJWTAuth()->auth($token);
        if (!$result) {
            throw new BadRequestHttpException('Token Error');
        }
    }

    protected function getJWTAuth()
    {
        $setting = $this->setting('storage', []);
        $secretKey = !empty($setting['cloud_secret_key']) ? $setting['cloud_secret_key'] : '';

        return new JWTAuth($secretKey);
    }
}
