<?php
namespace Topxia\WebBundle\Controller;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

class ClassroomManageController extends BaseController
{   
    public function indexAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:index.html.twig",array(
            'classroom'=>$classroom));
    }

    public function studentsAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:students.html.twig",array(
            'classroom'=>$classroom));
    }

    public function teachersAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:teachers.html.twig",array(
            'classroom'=>$classroom));
    }

    public function setInfoAction($id,Request $request)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod()=="POST"){

            $class=$request->request->all();

            $this->setFlashMessage('success',"基本信息设置成功！");

            $classroom=$this->getClassroomService()->updateClassroom($id,$class);
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set-info.html.twig",array(
            'classroom'=>$classroom));
    }

    public function setAction($id,Request $request)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if ($this->setting('vip.enabled')) {
            $levels = $this->getLevelService()->findEnabledLevels();
        } else {
            $levels = array();
        }

        if($request->getMethod()=="POST"){

            $class=$request->request->all();

            if($class['vipLevelId']=="") $class['vipLevelId']=0;

            $this->setFlashMessage('success',"设置成功！");

            $classroom=$this->getClassroomService()->updateClassroom($id,$class);
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set.html.twig",array(
            'levels' => $this->makeLevelChoices($levels),
            'classroom'=>$classroom));
    }

    public function setPictureAction($id,Request $request)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod()=="POST"){

            $file = $request->files->get('picture');
            if (!FileToolkit::isImageFile($file)) {
                return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
            }

            $filenamePrefix = "classroom_{$classroom['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $ext = $file->getClientOriginalExtension();
            $filename = $filenamePrefix . $hash . '.' . $ext;

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
            $file = $file->move($directory, $filename);

            $fileName = str_replace('.', '!', $file->getFilename());

            return $this->redirect($this->generateUrl('classroom_manage_picture_crop', array(
                'id' => $classroom['id'],
                'file' => $fileName)
            ));
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set-picture.html.twig",array(
            'classroom'=>$classroom));
    }

    public function pictureCropAction(Request $request, $id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);
      
        //@todo 文件名的过滤
        $filename = $request->query->get('file');
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $this->getClassroomService()->changePicture($classroom['id'], $pictureFilePath, $c);
            return $this->redirect($this->generateUrl('classroom_manage_set_picture', array('id' => $classroom['id'])));
        }

        try {
        $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(480)->heighten(270);

        $assets = $this->container->get('templating.helper.assets');
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;
        $pictureUrl = ltrim($pictureUrl, ' /');
        $pictureUrl = $assets->getUrl($pictureUrl);

        return $this->render('TopxiaWebBundle:ClassroomManage:picture-crop.html.twig', array(
            'classroom' => $classroom,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }
    
    public function coursesAction($id)
    {   
        $userIds = array();
        $coinPrice=0;
        $price=0;

        $classroom=$this->getClassroomService()->getClassroom($id);

        $classroomCourses=$this->getClassroomService()->getAllCourses($id);
        
        $courseIds=ArrayToolkit::column($classroomCourses,'courseId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $coinPrice+=$course['coinPrice'];
            $price+=$course['price'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:ClassroomManage:courses.html.twig",array(
            'classroom'=>$classroom,
            'classroomCourses'=>$classroomCourses,
            'courses'=>$courses,
            'price'=>$price,
            'coinPrice'=>$coinPrice,
            'users'=>$users));
    }


    public function coursesSelectAction(Request $request,$id)
    {
        $data=$request->request->all();

        $ids=$data['ids'];

        $ids=explode(",", $ids);

        foreach ($ids as $key => $value) {
            
            $course=$this->getClassroomService()->getCourseByClassroomIdAndCourseId($id,$value);

            if(empty($course))
            $this->getClassroomService()->addCourse($id,$value);
        }

        $this->setFlashMessage('success',"课程添加成功");

        return new Response('success');
    }

    public function publishAction($id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);

        $this->getClassroomService()->publishClassroom($id);

        return new Response("success");
    }

    public function closeAction($id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);

        $this->getClassroomService()->closeClassroom($id);

        return new Response("success");
    }

    private function makeLevelChoices($levels)
    {
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }
        return $choices;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}