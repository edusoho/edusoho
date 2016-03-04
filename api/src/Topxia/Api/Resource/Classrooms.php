<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Classrooms extends BaseResource
{
    public function discoveryColumn(Application $app, Request $request)
    {
        $result = $request->query->all();

        $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($result['categoryId']);
        $conditions['categoryIds'] = array_merge(array($result['categoryId']), $childrenIds);
        unset($conditions['categoryId']);

        if ($result['orderType'] == 'hot') {
            $orderBy = 'studentNum';
        } elseif ($result['orderType'] == 'new') {
            $orderBy = 'createdTime';
        } else {
            $orderBy = 'recommendedSeq';
        }

        $classrooms = $this->getClassroomService()->searchClassrooms($conditions, array($orderBy, 'desc'), 0, $result['showCount']);

        return $classrooms;
    }

    public function get(Application $app, Request $request)
    {
    }

    public function post(Application $app, Request $request)
    {
    }

    public function filter(&$res)
    {
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
