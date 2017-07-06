<?php

namespace Codeages\Weblib\Auth;

interface KeyProvider
{
    /**
     * @param $id
     * @return AccessKey
     */
    public function get($id);
}