<?php

namespace Codeages\Biz\Framework\Session\Service;

interface SessionService
{
    public function saveSession($session);

    public function deleteSessionBySessId($sessId);

    public function getSessionBySessId($sessId);

    public function gc();
}
