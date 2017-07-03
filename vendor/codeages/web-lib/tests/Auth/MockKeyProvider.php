<?php

namespace Codeages\Weblib\Auth;

class MockKeyProvider implements KeyProvider
{
    public function get($id)
    {
        $keys = array(
            'key_ok' => new AccessKey(
                'key_ok',
                'key_ok_secret',
                'active'
            ),
            'key_localhost' => new AccessKey(
                'key_localhost',
                'key_localhost_secret',
                'active',
                array('127.0.0.1', '::1')
            ),
        );

        if (!isset($keys[$id])) {
            return null;
        }

        return $keys[$id];
    }
}