<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

class LiveAuthController extends BaseController
{
    public function indexAction(Request $request)
    {
        $email = $request->get('email');
        $k = $request->get('k');

        $matched = preg_match('/^c(\d+)u(\d+)t(\d+)s(\w+)$/', $k, $matches);

        if (empty($matched)) {
            return new Response('fail');
        }

        $courseId = $matches[1];
        $userId = $matches[2];
        $timestamp = $matches[3];
        $sign = $matches[4];

        if (empty($courseId) or empty($userId) or empty($timestamp) or (empty($sign))) {
            return new Response('fail');
        }

        $expectSign = $this->makeSign("c{$courseId}u{$userId}t{$timestamp}");

        if ($expectSign != $sign) {
            return new Response('fail');
        }

        if (!$this->getCourseService()->isCourseStudent($courseId, $userId)) {
            return new Response('fail');
        }

        return new Response('pass');

    }

    protected function makeSign($string)
    {
        $secret = $this->container->getParameter('secret');
        return md5($string . $secret);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}