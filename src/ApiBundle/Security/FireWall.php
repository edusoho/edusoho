<?php

namespace ApiBundle\Security;

use ApiBundle\ApiBundle;
use Symfony\Component\HttpFoundation\Request;

class FireWall
{
    public static function isInWhiteList(Request $request)
    {
        static $whiteList = array(
            'GET'  => array(
                ApiBundle::API_PREFIX.'/users/\d+',
            ),
            'POST' => array(
                ApiBundle::API_PREFIX.'/tokens',
            )
        );

        if (!empty($matches = $whiteList[$request->getMethod()])) {
            $path = rtrim($request->getPathInfo(), '/');
            foreach ($matches as $whitePath) {
                $whitePath = str_replace('/', '\/', $whitePath);
                if (preg_match("/^{$whitePath}$/", $path)) {
                    return true;
                }
            }
        }

        return false;

    }
}