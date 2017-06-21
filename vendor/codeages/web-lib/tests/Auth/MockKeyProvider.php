<?php

namespace Codeages\Weblib\Auth;

class MockKeyProvider implements KeyProvider
{
    public function get($id)
    {
        $keys = array(
            'test_key_id_1' => new AccessKey('test_key_id_1', 'test_key_secret_1', 'active', time() + 3600),
            'test_key_id_2' => new AccessKey('test_key_id_2', 'test_key_secret_2', 'active', time() + 3600),
        );

        if (!isset($keys[$id])) {
            return null;
        }

        return $keys[$id];
    }
}