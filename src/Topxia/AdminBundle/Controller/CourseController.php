<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
class CourseController extends BaseController
{

    public function indexAction (Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('title', 'text', array('required' => false))
            ->add('nickname', 'text', array('required' => false))
            ->getForm();
        $form->bind($request);

        $conditions = $form->getData();
        $convertedConditions = $this->convertConditions($conditions);

        $count = $this->getCourseService()->searchCourseCount($convertedConditions);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses($convertedConditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Course:index.html.twig', array(
            'courses' => $courses ,
            'form' => $form->createView(),
            'paginator' => $paginator));
    }

    public function deleteAction(Request $request, $id)
    {

        $result = $this->getCourseService()->deleteCourse($id);
        return $this->createJsonResponse(true);
    }

    public function openAction(Request $request, $id)
    {
        $course = $this->getCourseService()->publishCourse($id);
        if(empty($course)) {
            return $this->createJsonResponse(false);
        } else {
            return $this->createJsonResponse(true);
        }
    }

    public function closeAction(Request $request, $id)
    {
        $course = $this->getCourseService()->closeCourse($id);
        if(empty($course)) {
            return $this->createJsonResponse(false);
        } else {
            return $this->createJsonResponse(true);
        }
    }

    public function categoryAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Category:embed', array(
            'group' => 'course',
            'layout' => 'TopxiaAdminBundle:Course:layout.html.twig',
        ));
    }

    private function convertConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            if (empty($user)) {
                throw $this->createNotFoundException(sprintf("昵称为%s的用户不存在", $conditions['nickname']));
            }
            $conditions['userId'] = $user['id'];
        }
        unset($conditions['nickname']);

        if (empty($conditions['title'])) {
            unset($conditions['title']);
        }
        return $conditions;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}