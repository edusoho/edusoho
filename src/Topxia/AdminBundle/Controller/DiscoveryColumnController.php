<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DiscoveryColumnController extends BaseController
{
    public function deleteAction(Request $request, $id)
    {
        $result = $this->getDiscoveryColumnService()->deleteDiscoveryColumn($id);

        if ($result > 0) {
            return $this->createJsonResponse(array('status' => 'ok'));
        } else {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function indexAction(Request $request)
    {
        $discoveryColumns = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();

        foreach ($discoveryColumns as $key => $discoveryColumn) {
            $conditions = array();

            if ($discoveryColumn['type'] == 'classroom') {
                $conditions['status']   = 'published';
                $conditions['showable'] = 1;

                if ($discoveryColumn['orderType'] == 'recommend') {
                    $conditions['recommended'] = 1;
                }

                if ($discoveryColumn['categoryId']) {
                    $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($discoveryColumn['categoryId']);
                    $conditions['categoryIds'] = array_merge(array($discoveryColumn['categoryId']), $childrenIds);
                }

                $classrooms = $this->getClassroomService()->searchClassrooms($conditions, array('createdTime', 'desc'), 0, $discoveryColumn['showCount']);

                $discoveryColumns[$key]['count'] = count($classrooms);
            } else {
                if ($discoveryColumn['orderType'] == 'recommend') {
                    $conditions['recommended'] = 1;
                }

                $conditions['categoryId'] = $discoveryColumn['categoryId'];

                if ($conditions['categoryId'] == 0) {
                    unset($conditions['categoryId']);
                }

                if ($discoveryColumn['type'] == 'live') {
                    $conditions['type'] = 'live';
                } else {
                    $conditions['type'] = 'normal';
                }

                $conditions['parentId'] = 0;
                $conditions['status']   = 'published';
                $courses                = $this->getCourseService()->searchCourses($conditions, 'createdTime', 0, $discoveryColumn['showCount']);

                if ($discoveryColumn['orderType'] == 'recommend' && count($courses) < $discoveryColumn['showCount']) {
                    $conditions['recommended'] = 0;
                    $unrecommendCourses        = $this->getCourseService()->searchCourses($conditions, 'createdTime', 0, $discoveryColumn['showCount'] - count($courses));
                    $courses                   = array_merge($courses, $unrecommendCourses);
                }

                $discoveryColumns[$key]['count'] = count($courses);
            }
        }

        return $this->render('TopxiaAdminBundle:DiscoveryColumn:index.html.twig', array(
            'discoveryColumns' => $discoveryColumns
        ));
    }

    public function createAction(Request $request)
    {
        $categoryId = array();

        if ($request->getMethod() == 'POST') {
            $conditions                = $request->request->all();
            $conditions['createdTime'] = time();

            if (empty($conditions['categoryId'])) {
                $conditions['categoryId'] = 0;
            }

            if ($conditions['type'] == 'live') {
                $conditions['orderType'] = '';
            }

            $discoveryColumn = $this->getDiscoveryColumnService()->findDiscoveryColumnByTitle($conditions['title']);

            if (empty($discoveryColumn) && $conditions['title']) {
                $discoveryColumn = $this->getDiscoveryColumnService()->addDiscoveryColumn($conditions);
            }

            return $this->redirect($this->generateUrl('admin_discovery_column_index'));
        }

        if (empty($categoryId)) {
            $categoryId = 0;
        }

        if (empty($discoveryColumn)) {
            $discoveryColumn = array();
        }

        return $this->render('TopxiaAdminBundle:DiscoveryColumn:discovery-column-modal.html.twig', array(
            'discoveryColumn' => $discoveryColumn,
            'categoryId'      => $categoryId
        ));
    }

    public function editAction(Request $request, $id)
    {
        $discoveryColumn = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);

        if (empty($discoveryColumn)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $conditions = $request->request->all();

            if (empty($conditions['categoryId'])) {
                $conditions['categoryId'] = 0;
            }

            if ($conditions['type'] == 'live') {
                $conditions['orderType'] = '';
            }

            $discoveryColumn = $this->getDiscoveryColumnService()->updateDiscoveryColumn($id, $conditions);
            return $this->redirect($this->generateUrl('admin_discovery_column_index'));
        }

        return $this->render('TopxiaAdminBundle:DiscoveryColumn:discovery-column-modal.html.twig', array(
            'discoveryColumn' => $discoveryColumn,
            'categoryId'      => $discoveryColumn['categoryId']
        ));
    }

    public function checkTitleAction(Request $request, $id)
    {
        $title           = $request->query->get('value');
        $discoveryColumn = $this->getDiscoveryColumnService()->findDiscoveryColumnByTitle($title);

        if (empty($title)) {
            $response = array('success' => false, 'message' => '请输入栏目名称！');
        } elseif ($discoveryColumn && $title && $discoveryColumn[0]['id'] != $id) {
            $response = array('success' => false, 'message' => '该栏目名称已经存在！');
        } else {
            $response = array('success' => true);
        }

        return $this->createJsonResponse($response);
    }

    public function sortAction(Request $request)
    {
        $data = $request->request->get('data');
        $ids  = ArrayToolkit::column($data, 'id');

        if (!empty($ids)) {
            $this->getDiscoveryColumnService()->sortDiscoveryColumns($ids);
        }

        return $this->createJsonResponse(true);
    }

    protected function getDiscoveryColumnService()
    {
        return $this->getServiceKernel()->createService('DiscoveryColumn.DiscoveryColumnService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
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
