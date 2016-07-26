<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 16/7/26
 * Time: 09:21
 */

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class BaseOpenCourseController extends BaseController
{
    protected function createRefererLog(Request $request, $course)
    {
        $fields = array(
            'targetId'        => $course['id'],
            'targetType'      => 'openCourse',
            'refererUrl'      => $request->server->get('HTTP_REFERER'),
            'uri'             => $request->getUri(),
            'targetInnerType' => $course['type'],
            'ip'              => $request->getClientIp(),
            'userAgent'       => $request->headers->get("user-agent")
        );
        $uv = $request->cookies->get('uv');
        if (empty($uv)) {
            return false;
        }
        $refererLog = $this->getRefererLogService()->addRefererLog($fields);
        $this->updatevisitRefererToken($refererLog, $request, $uv);
    }

    protected function updatevisitRefererToken($refererLog, Request $request, $uv)
    {
        $token = $this->getRefererLogService()->getOrderRefererByUv($uv);

        $key                  = $refererLog['targetType'].'_'.$refererLog['targetId'];
        $token['data'][$key]  = $refererLog['id'];
        $token['expiredTime'] = strtotime(date('Y-m-d').' 23:59:59');
        if (empty($token['id'])) {
            $token['uv'] = $uv;
            $this->getRefererLogService()->createOrderReferer($token);
        } else {
            $this->getRefererLogService()->updateOrderReferer($token['id'], $token);
        }
    }

    protected function getRefererLogService()
    {
        return $this->getServiceKernel()->createService('RefererLog.RefererLogService');
    }
}
