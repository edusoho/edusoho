<?php
namespace Custom\WebBundle\Controller;
use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseMaterialController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在，或已删除。");
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }


        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->getMaterialCount($id),
            20
        );

        $materials = $this->getMaterialService()->findCourseMaterials(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render("TopxiaWebBundle:CourseMaterial:index.html.twig", array(
            'course' => $course,
            'lessons'=>$lessons,
            'materials' => $materials,
            'paginator' => $paginator,
        ));
    }

    

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

}