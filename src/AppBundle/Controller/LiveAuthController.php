<?php

namespace AppBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LiveAuthController extends BaseController
{
    public function indexAction(Request $request)
    {
        $k = $request->get('k');

        $matched = preg_match('/^c(\d+)u(\d+)t(\d+)s(\w+)$/', $k, $matches);

        if (empty($matched)) {
            return new Response('fail');
        }

        $courseId = $matches[1];
        $userId = $matches[2];
        $timestamp = $matches[3];
        $sign = $matches[4];

        if (empty($courseId) || empty($userId) || empty($timestamp) || (empty($sign))) {
            return new Response('fail');
        }

        $expectSign = $this->makeSign("c{$courseId}u{$userId}t{$timestamp}");

        if ($expectSign != $sign) {
            return new Response('fail');
        }

        if (!$this->getCourseMemberService()->isCourseStudent($courseId, $userId)) {
            return new Response('fail');
        }

        return new Response('pass');
    }

    protected function makeSign($string)
    {
        $secret = $this->container->getParameter('secret');

        return md5($string.$secret);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
