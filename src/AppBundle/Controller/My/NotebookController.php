<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class NotebookController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId'             => $user['id'],
            'noteNumGreaterThan' => 0.1
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->countMembers($conditions),
            10
        );

        $courseMembers = $this->getCourseMemberService()->searchMembers($conditions, $orderBy = array(), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($courseMembers, 'courseId'));

        return $this->render('my/notebook/index.html.twig', array(
            'courseMembers' => $courseMembers,
            'paginator'     => $paginator,
            'courses'       => $courses
        ));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}