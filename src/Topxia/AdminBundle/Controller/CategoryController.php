<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseController
{
    public function embedAction($group, $layout)
    {
        $group = $this->getCategoryService()->getGroupByCode($group);
        if (empty($group)) {
            throw $this->createNotFoundException();
        }
        $categories = $this->getCategoryService()->getCategoryTree($group['id']);
        return $this->render('TopxiaAdminBundle:Category:embed.html.twig', array(
            'group' => $group,
            'categories' => $categories,
            'layout' => $layout
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->createCategory($request->request->all());
            return $this->renderTbody($category['groupId']);
        }

        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'description'=>'',
            'groupId' => (int) $request->query->get('groupId'),
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'icon' => ''
        );

        return $this->render('TopxiaAdminBundle:Category:modal.html.twig', array(
            'category' => $category
        ));
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->updateCategory($id, $request->request->all());
            return $this->renderTbody($category['groupId']);
        }

        return $this->render('TopxiaAdminBundle:Category:modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        $this->getCategoryService()->deleteCategory($id);

        return $this->renderTbody($category['groupId']);
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getCategoryService()->isCategoryCodeAvaliable($code, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已被占用，请换一个。');
        }

        return $this->createJsonResponse($response);
    }

    public function uploadFileAction (Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $originalFile = $this->get('request')->files->get('file');
            $file = $this->getUploadFileService()->addFile('category', 0, array('isPublic' => 1), 'local', $originalFile);
            $file['hashId'] = "/files/".$file['hashId'];
            return new Response(json_encode($file));
        }
    }

    private function renderTbody($groupId)
    {
        $group = $this->getCategoryService()->getGroup($groupId);
        $categories = $this->getCategoryService()->getCategoryTree($groupId);
        return $this->render('TopxiaAdminBundle:Category:tbody.html.twig', array(
            'categories' => $categories,
            'group' => $group
        ));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}