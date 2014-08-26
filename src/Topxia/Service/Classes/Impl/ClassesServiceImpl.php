<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Classes\ClassesService;
use Topxia\Service\Common\BaseService;

class ClassesServiceImpl extends BaseService implements ClassesService
{
    public function getClass($id)
    {
        return $this->getClassesDao()->getClass($id);
    }

    public function searchClasses($conditions, $sort = array(), $start, $limit) 
    {
        return $this->getClassesDao()->searchClasses($conditions, $sort, $start, $limit);
    }

    public function searchClassCount($conditions)
    {
        return $this->getClassesDao()->searchClassCount($conditions);
    }

    public function createClass($class)
    {
        return $this->getClassesDao()->createClass($class);
    }

    private function getClassesDao ()
    {
        return $this->createDao('Classes.ClassesDao');
    }

}