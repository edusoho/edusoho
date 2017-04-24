<?php

namespace Biz\System\Service;

interface SessionService
{
    public function get($id);

    public function clear($id);

    public function clearByUserId($userId);

    public function deleteInvalidSession($sessionTime, $limit);
}
