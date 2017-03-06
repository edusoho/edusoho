<?php

namespace AppBundle\Controller;

use Biz\RefererLog\Service\RefererLogService;
use Symfony\Component\HttpFoundation\Request;

class BaseOpenCourseController extends BaseController
{
    protected function createRefererLog(Request $request, $course)
    {
        $uri = $request->getUri();
        $clientIp = $request->getClientIp();
        $userAgent = $request->headers->get('user-agent');
        $refererUrl = $request->query->get('refererUrl', '');
        if (empty($refererUrl)) {
            $refererUrl = $request->server->get('HTTP_REFERER');
        }

        $fields = array(
            'targetId' => $course['id'],
            'targetType' => 'openCourse',
            'refererUrl' => $refererUrl,
            'uri' => $uri,
            'targetInnerType' => $course['type'],
            'ip' => $clientIp,
            'userAgent' => $userAgent,
        );

        $uv = $request->cookies->get('uv');
        if (empty($uv)) {
            return false;
        }
        $refererLog = $this->getRefererLogService()->addRefererLog($fields);
        $this->updatevisitRefererToken($refererLog, $request, $uv);

        return true;
    }

    protected function updatevisitRefererToken($refererLog, Request $request, $uv)
    {
        $token = $this->getRefererLogService()->getOrderRefererByUv($uv);

        $key = $refererLog['targetType'].'_'.$refererLog['targetId'];
        $token['data'][$key] = $refererLog['id'];
        $token['expiredTime'] = strtotime(date('Y-m-d').' 23:59:59');
        if (empty($token['id'])) {
            $token['uv'] = $uv;
            $this->getRefererLogService()->createOrderReferer($token);
        } else {
            $this->getRefererLogService()->updateOrderReferer($token['id'], $token);
        }
    }

    /**
     * @return RefererLogService
     */
    protected function getRefererLogService()
    {
        return $this->getBiz()->service('RefererLog:RefererLogService');
    }
}
