<?php

namespace Codeages\Biz\Framework\Session\Storage;

interface SessionStorage
{
    public function save($session);

    public function delete($sessId);

    public function get($sessId);

    public function gc();
}
