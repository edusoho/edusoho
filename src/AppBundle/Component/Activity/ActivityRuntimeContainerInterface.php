<?php

namespace AppBundle\Component\Activity;

interface ActivityRuntimeContainerInterface
{
    const ROUTE_SHOW = 'show';
    const ROUTE_CREATE = 'create';
    const ROUTE_UPDATE = 'update';

    public function create();

    public function show($activity);

    public function update($task);
}
