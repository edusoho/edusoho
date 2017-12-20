<?php

namespace AppBundle\Component\Activity;

interface ActivityRuntimeContainerInterface
{
    public function create();

    public function show($activity);

    public function update($task);

    public function invoke($activityType, $action);
}
