<?php

namespace Codeages\Biz\Framework\Session\Service;

interface SessionService
{
    public function createSession($session);

    public function updateSessionBySessId($sessId, $session);

    public function getSessionBySessId($sessId);

    public function deleteSessionBySessId($sessId);

    public function gc();

    public function countLogined($gtSessTime);

    public function countTotal($gtSessTime);

    public function searchSessions($condition, $orderBy, $start, $limit);

    public function countSessions($condition);
}