<?php 
namespace Topxia\Service\System;

interface SessionService
{
    public function get($id);

    public function clear ($id);

    public function clearByUserId ($userId);
}