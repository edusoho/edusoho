<?php
namespace Custom\WebBundle\Controller;
use Topxia\WebBundle\Controller\BaseController as BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\LiveClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CourseManageController extends BaseController
{

        public function indexAction(Request $request, $id)
        {
                 return $this->forward('CustomWebBundle:CourseManage:base',  array('id' => $id));
        }
        public function baseAction(Request $request, $id)
        {
	$course = $this->getCourseService()->tryManageCourse($id);
              $courseSetting = $this->getSettingService()->get('course', array());
              if($request->getMethod() == 'POST'){
                    $data = $request->request->all();
                    $this->getCustomCourseService()->updateCourse($id, $data);
                    $this->setFlashMessage('success', '课程基本信息已保存！');
                    return $this->redirect($this->generateUrl('course_manage_base',array('id' => $id))); 
              }

            $tags = $this->getTagService()->findTagsByIds($course['tags']);
            if ($course['type'] == 'live') {
                $client = LiveClientFactory::createClient();
                $liveCapacity = $client->getCapacity();
            } else {
                $liveCapacity = null;
            }
            return $this->render('CustomWebBundle:CourseManage:base.html.twig', array(
    			'course' => $course,
                'tags' => ArrayToolkit::column($tags, 'name'),
                'liveCapacity' => empty($liveCapacity['capacity']) ? 0 : $liveCapacity['capacity'],
                'liveProvider' => empty($liveCapacity['code']) ? 0 : $liveCapacity['code'],
    		));
       }


    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCustomCourseService(){
         return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }

    private function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}