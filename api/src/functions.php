<?php
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

function filter($data, $type)
{
    $class = 'Topxia\\Api\\Filter\\' .  ucfirst($type) . 'Filter';
    $filter = new $class();
    return $filter->filter($data);
}

function convert($data, $type)
{
    $class = 'Topxia\\Api\\Convert\\' .  ucfirst($type) . 'Convert';
    $convert = new $class();
    return $convert->convert($data);
}

function setCurrentUser($user)
{
	$currentUser = new CurrentUser();
    $currentUser->fromArray($user);
    ServiceKernel::instance()->setCurrentUser($currentUser);
}