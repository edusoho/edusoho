<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\WebBundle\Controller\BaseController

/**
 * 作业评分控制器.
**/
class HomeworkReviewController extends BaseController
{
    /**
     * 显示作业答卷评分页面.
     * @param request
     * @param id , 作业答卷id.
    **/
    public function randomizeAction(Request $request, $id)
    {
        // list ($course, $member) = $this->buildCourseLayoutData($request, $id);
        // if(empty($member)) {
        //     $user = $this->getCurrentUser();
        //     $member = $this->getCourseService()->becomeStudentByClassroomJoined($id, $user->id);
        //     if(isset($member["id"])) {
        //         $course['studentNum'] ++ ;
        //     }
        // }

        // $this->getCourseService()->hitCourse($id);
        //     $items = $this->getCourseService()->getCourseItems($course['id']);

        // return $this->render("CustomWebBundle:Course:{$course['type']}-show.html.twig", array(
        //     'course' => $course,
        //     'member' => $member,
        //     'items' => $items,
        // ));

    }

    /**
     * 获取作业服务.
    **/
    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }
}