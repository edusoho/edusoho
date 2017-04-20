<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Classrooms extends BaseResource
{
    public function discoveryColumn(Application $app, Request $request)
    {
        $defaultQuery = array(
            'orderType' => '',
            'showCount' => ''
        );

        $result     = array_merge($defaultQuery, $request->query->all());
        $conditions = array();
        if ($result['categoryId']) {
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($result['categoryId']);
            $conditions['categoryIds'] = array_merge(array($result['categoryId']), $childrenIds);
        }

        if ($result['orderType'] == 'hot') {
            $orderBy = 'studentNum';
        } elseif ($result['orderType'] == 'recommend') {
            $orderBy                   = 'recommendedSeq';
            $conditions['recommended'] = 1;
        } else {
            $orderBy = 'createdTime';
        }
        if (empty($result['showCount'])) {
            $result['showCount'] = 6;
        }

        $conditions['status']   = 'published';
        $conditions['showable'] = 1;
        $classrooms             = $this->getClassroomService()->searchClassrooms($conditions, array($orderBy=> 'DESC'), 0, $result['showCount']);

        $total      = count($classrooms);
        $classrooms = $this->filter($classrooms);

        return $this->wrap($classrooms, $total);
    }

    public function get(Application $app, Request $request)
    {
    }

    public function post(Application $app, Request $request)
    {
    }

    public function filter($res)
    {
        return $this->multicallFilter('Classroom', $res);
    }

    protected function multicallFilter($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
        }

        return $res;
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}
