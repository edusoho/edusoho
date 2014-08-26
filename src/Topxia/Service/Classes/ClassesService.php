<?php

namespace Topxia\Service\Classes;

interface ClassesService
{
    /**
    *ClassService API
    */
    public function getClass($id);

    public function searchClasses($conditions, $sort = 'latest', $start, $limit);

    public function searchClassCount($conditions);

    public function createClass($class);
}