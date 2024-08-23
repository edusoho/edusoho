<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends BaseController
{
    public function sampleAction(Request $request)
    {
        $sessionId = $request->getSession()->getId();
        //        $lastFlushTime = $request->getSession()->get('online_flush_time', 0);

        $cookieName = 'online-uuid';

        $uuid = $request->cookies->get($cookieName, $this->generateGuid());

        if (!empty($sessionId)) {
            $this->get('user.online_track')->track($uuid);
        }

        $response = new Response('true');
        $response->headers->setCookie(new Cookie($cookieName, $uuid));

        return $response;
    }

    protected function generateGuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid, 12, 4).$hyphen
                .substr($charid, 16, 4).$hyphen
                .substr($charid, 20, 12);

            return $uuid;
        }
    }
}
