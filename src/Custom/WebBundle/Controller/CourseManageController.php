<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\NumberToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\LiveClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Topxia\WebBundle\Controller\CourseController as BaseCourseManageController;

class CourseManageController extends BaseCourseManageController
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
            if($course['type']=='periodic'){
                var_dump($course['type']);
                if($course['rootId']==0)
                    $data['rootId'] = intval($id);
                if(!empty($data['startTime']))
                    $data['startTime'] = strtotime($data['startTime']);
                if(!empty($data['endTime']))
                    $data['endTime'] = strtotime($data['endTime']);
            }
            $this->getCourseService()->customUpdateCourse($id, $data);
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
        $default = $this->getSettingService()->get('default', array());

		return $this->render('CustomWebBundle:CourseManage:base.html.twig', array(
			'course' => $course,
            'tags' => ArrayToolkit::column($tags, 'name'),
            'liveCapacity' => empty($liveCapacity['capacity']) ? 0 : $liveCapacity['capacity'],
            'liveProvider' => empty($liveCapacity['code']) ? 0 : $liveCapacity['code'],
            'default'=> $default
		));
	}

}