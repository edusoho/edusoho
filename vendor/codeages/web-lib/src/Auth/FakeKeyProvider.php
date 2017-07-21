<?php

namespace Codeages\Weblib\Auth;

class FakeKeyProvider implements KeyProvider
{

    /**
     * @param $id
     * @return AccessKey
     */
    public function get($id)
    {
        return new AccessKey($id);
    }
}