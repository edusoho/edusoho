<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Form\CategoryType;
use Topxia\Service\Common\ServiceException;

class CategoryController extends BaseController
{
    public function indexAction(Request $request)
    {
        $groupId = (int) $request->query->get('groupId');
        $group = $this->getCategoryService()->getGroup($groupId);
        if (empty($group)) {
            throw $this->createNotFoundException();
        }

        $categories = $this->getCategoryService()->getCategoryTree($groupId);
        return $this->render('TopxiaAdminBundle:Category:index.html.twig', array(
            'group' => $group,
            'categories' => $categories,
        ));
    }

    public function createAction(Request $request)
    {
        $groupId = (int) $request->query->get('groupId');
        $parentId = (int) $request->query->get('parentId', 0);
        $form = $this->createForm(new CategoryType);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $data['groupId'] = $groupId;
                $data['parentId'] = $parentId;
                $data['weight'] = (int) $data['weight'];
                try {
                    $category = $this->getCategoryService()->saveCategory($data);
                    return $this->createJsonResponse(array('status' => 'ok'));
                } catch (ServiceException $e) {
                    return $this->createJsonResponse(array('status' => 'error', 'error' => array('message' => $e->getMessage())));
                }
            }
        }
        return $this->render('TopxiaAdminBundle:Category:category-save-modal.html.twig', array(
            'form' => $form->createView(),
            'groupId' => $groupId,
            'parentId' => $parentId,
            'type' => 'create'
        ));
    }

    public function codeCheckAction(Request $request)
    {
        $code = $request->query->get('value');
        $category = $this->getCategoryService()->getCategoryByCode($code);
        if (empty($category)) {
            return $this->createJsonResponse(array('success' => true, 'message' => 'URI可以使用'));
        }
        return $this->createJsonResponse(array('success' => false, 'message' => 'URI已被占用'));
    }

    public function codeEditCheckAction(Request $request, $categoryId)
    {
        $code = $request->query->get('value');
        $category = $this->getCategoryService()->getCategory($categoryId);
        $categoryByCode = $this->getCategoryService()->getCategoryByCode($code);

        if (empty($categoryByCode)) {
            return $this->createJsonResponse(array('success' => true, 'message' => 'URI可以使用'));
        }

        $diff = array_diff($category, $categoryByCode);
        if (!empty($diff) && !empty($categoryByCode)) {
            return $this->createJsonResponse(array('success' => false, 'message' => 'URI已被占用'));
        }
        return $this->createJsonResponse(array('success' => true, 'message' => 'URI可以使用'));
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(new CategoryType, $category);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                try {
                    $category = $this->getCategoryService()->saveCategory($data);
                    return $this->createJsonResponse(array('status' => 'ok'));
                } catch (ServiceException $e) {
                    return $this->createJsonResponse(array('status' => 'ok', 'error' => array('message' => $e->getMessage())));
                }
            }
        }

        return $this->render('TopxiaAdminBundle:Category:category-save-modal.html.twig', array(
            'form' => $form->createView(),
            'category' => $category,
            'group' => $this->getCategoryService()->getGroup($category['groupId']),
            'type' => 'edit'
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        
    }

    public function groupAction (Request $request)
    {
        //  @todo, 暂时就取１００个够了吧。
        $groups = $this->getCategoryService()->getGroups(0, 100);
        return $this->render('TopxiaAdminBundle:Category:group.html.twig', array(
            'groups' => $groups,
        ));
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}