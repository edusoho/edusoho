<?php
/**
 * User: Edusoho V8
 * Date: 19/10/2016
 * Time: 19:00
 */

namespace Topxia\Service\User;


interface UserActiveService
{

    public function createActiveUser($userId);

    public function isActiveUser($userId);

    public function analysisActiveUser($startTime, $endTime);


}