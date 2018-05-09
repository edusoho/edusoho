<?php

namespace AppBundle\Component\Activity;

interface ActivityRuntimeContainerInterface
{
    const ROUTE_SHOW = 'show';
    const ROUTE_CREATE = 'create';
    const ROUTE_UPDATE = 'update';
    const ROUTE_CONTENT = 'content';
    const ROUTE_FINISH = 'finish';

    public function create($activity);

    public function show($activity);

    public function update($task);
}
