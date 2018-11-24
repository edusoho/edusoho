<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Biz\DiscoveryColumn\DiscoveryColumnException;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;
use Symfony\Component\HttpFoundation\Request;

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

    public function categoryTreeAction(Request $request)
    {
        $id = $request->query->get('id');
        $type = $request->query->get('type');
        if ($id) {
            $discoveryColumn = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);
        }

        return $this->render('admin/discovery-column/discovery-column-category.html.twig', array(
            'categoryId' => empty($discoveryColumn['categoryId']) ? 0 : $discoveryColumn['categoryId'],
            'type' => $type,
        ));
    }

    public function indexAction(Request $request)
    {
        $discoveryColumns = $this->getDiscoveryColumnService()->getDisplayData();

        return $this->render('admin/discovery-column/index.html.twig', array(
            'discoveryColumns' => $discoveryColumns,
        ));
    }

    public function createAction(Request $request)
    {
        $categoryId = array();

        if ($request->getMethod() == 'POST') {
            $conditions = $request->request->all();
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

        return $this->render('admin/discovery-column/discovery-column-modal.html.twig', array(
            'discoveryColumn' => $discoveryColumn,
            'categoryId' => $categoryId,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $discoveryColumn = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);

        if (empty($discoveryColumn)) {
            $this->createNewException(DiscoveryColumnException::NOTFOUND_DISCOVERY_COLUMN());
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

        return $this->render('admin/discovery-column/discovery-column-modal.html.twig', array(
            'discoveryColumn' => $discoveryColumn,
            'categoryId' => $discoveryColumn['categoryId'],
        ));
    }

    public function checkTitleAction(Request $request, $id)
    {
        $title = $request->query->get('value');
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
        $ids = ArrayToolkit::column($data, 'id');

        if (!empty($ids)) {
            $this->getDiscoveryColumnService()->sortDiscoveryColumns($ids);
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @return DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
