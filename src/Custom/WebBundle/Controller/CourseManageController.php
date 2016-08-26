<?php 
namespace Custom\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseManageController as BaseCourseManageController;

class CourseManageController extends BaseCourseManageController
{
    public function baseAction(Request $request, $id)
    {
        $course        = $this->getCourseService()->tryManageCourse($id);
        $courseSetting = $this->getSettingService()->get('course', array());

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $this->getCourseService()->updateCourse($id, $data);
            $this->setFlashMessage('success', '课程基本信息已保存！');
            return $this->redirect($this->generateUrl('course_manage_base', array('id' => $id)));
        }

        $tags = $this->getTagService()->findTagsByIds($course['tags']);

        $default = $this->getSettingService()->get('default', array());

        return $this->render('CustomWebBundle:CourseManage:base.html.twig', array(
            'course'  => $course,
            'tags'    => ArrayToolkit::column($tags, 'name'),
            'default' => $default
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }
}