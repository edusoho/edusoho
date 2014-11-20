<?php
namespace Topxia\WebBundle\Controller;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends BaseController
{
    public function allAction()
    {
        $categories = $this->getCategoryService()->findCategories(1);

        $data = array();
        foreach ($categories as $category) {
            $data[$category['id']] = array($category['name'], $category['parentId']);
        }

        return $this->createJsonResponse($data);
    }

    public function getMaterialAction(Request $request)
    {
        $query = $request->query->all();
        $material = $this->getCategoryService()->getMaterialCategoryByGradeIdAndSubjectId($query['gradeId'], $query['subjectId']);
        return $this->createJsonResponse($material);
    }

    public function getSubjectsAction(Request $request)
    {
        $query = $request->query->all();
        return $this->render('TopxiaAdminBundle:Knowledge:subject-options.html.twig', array(
            'gradeId' => $query['gradeId'],
        ));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}