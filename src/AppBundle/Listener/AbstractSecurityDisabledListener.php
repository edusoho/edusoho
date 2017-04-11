<?php

namespace AppBundle\Listener;

abstract class AbstractSecurityDisabledListener
{
    protected function isSecurityDisabledRequest($container, $request)
    {
        $prefixs = $container->getParameter('security_disabled_uri_prefixs');
        $path = $request->getPathInfo();
        foreach ($prefixs as $prefix) {
            $prefix = '/'.$prefix;
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
