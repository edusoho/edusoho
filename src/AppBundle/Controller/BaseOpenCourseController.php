<?php

namespace AppBundle\Controller;

use Biz\OpenCourse\Service\OpenCourseService;
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

    protected function _memberOperate(Request $request, $courseId)
    {
        $result = $this->_checkExistsMember($request, $courseId);

        if ($result['result']) {
            $fields = [
                'courseId' => $courseId,
                'ip' => $request->getClientIp(),
                'lastEnterTime' => time(),
            ];
            $member = $this->getOpenCourseService()->createMember($fields);
        } else {
            $member = $this->getOpenCourseService()->updateMember($result['member']['id'], ['lastEnterTime' => time()]);
        }

        return $member;
    }

    protected function _checkExistsMember(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();
        $userIp = $request->getClientIp();

        if (!$user->isLogin()) {
            $openCourseMember = $this->getOpenCourseService()->getCourseMemberByIp($courseId, $userIp);
        } else {
            $openCourseMember = $this->getOpenCourseService()->getCourseMember($courseId, $user['id']);
            if (!empty($user['verifiedMobile'])) {
                $member = $this->getOpenCourseService()->getCourseMemberByMobile($courseId, $user['verifiedMobile']);
                if ($member) {
                    $openCourseMember = $this->getOpenCourseService()->updateMember($member['id'], ['userId' => $user['id']]);
                }
            }
        }

        if ($openCourseMember) {
            return ['result' => false, 'message' => '课程用户已存在！', 'member' => $openCourseMember];
        }

        return ['result' => true];
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return RefererLogService
     */
    protected function getRefererLogService()
    {
        return $this->getBiz()->service('RefererLog:RefererLogService');
    }
}
