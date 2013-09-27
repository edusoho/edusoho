<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class ActivityManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        return $this->forward('TopxiaWebBundle:ActivityManage:base',  array('id' => $id));
    }

    public function baseAction(Request $request, $id)
    {
        $course = $this->getActivityService()->getActivity($id);
        $form = $this->createActivityBaseForm($course);
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $courseBaseInfo = $form->getData();
                $this->getActivityService()->updateActivity($id, $courseBaseInfo);
                $this->setFlashMessage('success', '活动基本信息已保存！');
                return $this->redirect($this->generateUrl('activity_manage_base',array('id' => $id))); 
            }
        }
        $course['strstartTime']=$course['startTime'];
        $course['strendTime']=$course['endTime'];
        $newcourse=$course;
        return $this->render('TopxiaWebBundle:ActivityManage:base.html.twig', array(
            'course' => $newcourse,
            'form' => $form->createView(),
        ));
    }

    public function detailAction(Request $request, $id)
    {
        
        $course = $this->getActivityService()->getActivity($id);

        if($request->getMethod() == 'POST'){
            $detail = $request->request->all();
            $detail['goals'] = (empty($detail['goals']) or !is_array($detail['goals'])) ? array() : $detail['goals'];
            $detail['audiences'] = (empty($detail['audiences']) or !is_array($detail['audiences'])) ? array() : $detail['audiences'];

            $this->getActivityService()->updateActivity($id, $detail);
            $this->setFlashMessage('success', '课程详细信息已保存！');

            return $this->redirect($this->generateUrl('activity_manage_detail',array('id' => $id))); 
        }

        return $this->render('TopxiaWebBundle:ActivityManage:detail.html.twig', array(
            'course' => $course
        ));
    }

    public function pictureAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getActivityService()->getActivity($id);
        if($request->getMethod() == 'POST'){
            $file = $request->files->get('picture');

            $filenamePrefix = "course_{$course['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $filename = $filenamePrefix . $hash . '.' . $file->getClientOriginalExtension();

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';

            $file = $file->move($directory, $filename);

            return $this->redirect($this->generateUrl('activity_manage_picture_crop', array(
                'id' => $course['id'],
                'file' => $file->getFilename())
            ));
        }

        return $this->render('TopxiaWebBundle:ActivityManage:picture.html.twig', array(
            'course' => $course,
        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getActivityService()->getActivity($id);

        //@todo 文件名的过滤
        $filename = $request->query->get('file');
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $this->getActivityService()->changeActivityPicture($course['id'], $pictureFilePath, $c);
            return $this->redirect($this->generateUrl('activity_manage_picture', array('id' => $course['id'])));
        }

        $imagine = new Imagine();
        $image = $imagine->open($pictureFilePath);

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(300)->heighten(300);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return $this->render('TopxiaWebBundle:ActivityManage:picture-crop.html.twig', array(
            'course' => $course,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function priceAction(Request $request, $id)
    {

        if ($request->getMethod() == 'POST') {
            $this->getActivityService()->updateActivity($id, $request->request->all());
            $this->setFlashMessage('success', '课程价格已经修改成功!');
        }

        $course = $this->getActivityService()->getActivity($id);
        return $this->render('TopxiaWebBundle:ActivityManage:price.html.twig', array(
            'course' => $course
        ));
        
    }

    public function teachersAction(Request $request, $id)
    {
        if($request->getMethod() == 'POST'){
            
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $field['experterid']=$data['ids'];
            $this->getActivityService()->setActivityTeachers($id,$field);
            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('activity_manage_teachers',array('id' => $id))); 

        }

        $teacherMembers = $this->getActivityService()->getActivity($id);
        $users = $this->getUserService()->findUsersByIds($teacherMembers['experterid']);

        $teachers = array();
        foreach ($users as $member) {
            if (empty($member['id'])) {
                continue;
            }
            $teachers[] = array(
                'id' => $member['id'],
                'nickname' => $member['nickname'],
                'avatar'  => $this->getWebExtension()->getFilePath($member['smallAvatar'], 'avatar.png'),
                'isVisible' => empty($member['isVisible']) ? true : false,
            );
        }
        return $this->render('TopxiaWebBundle:ActivityManage:teachers.html.twig', array(
            'course' => $this->getActivityService()->getActivity($id),
            'teachers' => $teachers
        ));
    }

    public function publishAction(Request $request, $id)
    {
        $this->getActivityService()->publishActivity($id);
        return $this->createJsonResponse(true);
    }

    public function teachersMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(array('nicknameLike'=>$likeString),array('createdTime', 'DESC'),0, 10);

        $teachers = array();
        foreach ($users as $user) {
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
                'isVisible' => 1,
            );
        }

        return $this->createJsonResponse($teachers);
    }

    public function picturesMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $photos = $this->getPhotoService()->searchPhotos(array('title'=>$likeString),array('createdTime', 'DESC'),0, 10);

        $newPhoto = array();
        foreach ($photos as $photo) {
            $newPhoto[] = array(
                'id' => $photo['id'],
                'nickname' => $photo['name'],
                'isVisible' => 1,
            );
        }

        return $this->createJsonResponse($newPhoto);
    }

    

    public function courseMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $users = $this->getCourseService()->searchCourses(array('title'=>$likeString),'latest',0, 10);

        $teachers = array();
        foreach ($users as $user) {
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['title'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallPicture'], 'avatar.png'),
                'isVisible' => 1,
            );
        }

        return $this->createJsonResponse($teachers);
    }





    public function courseAction(Request $request,$id){

        if($request->getMethod() == 'POST'){
            
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $field['courseId']=$data['ids'];
            $this->getActivityService()->setActivityCourse($id,$field);
            $this->setFlashMessage('success', '课程设置成功！');

            return $this->redirect($this->generateUrl('activity_manage_course',array('id' => $id))); 

        }

        $activity = $this->getActivityService()->getActivity($id);

        $courses = $this->getCourseService()->findCoursesByIds($activity['courseId']);

        $teachers = array();
        foreach ($courses as $course) {
            if(!empty($course['id'])){
                $teachers[] = array(
                    'id' => $course['id'],
                    'nickname' => $course['title'],
                    'avatar'  => $this->getWebExtension()->getFilePath($course['smallPicture'], 'avatar.png'),
                    'isVisible' => empty($course['isVisible']) ? true : false,
                );
            }
        }

        return $this->render('TopxiaWebBundle:ActivityManage:course.html.twig', array(
            'course' => $activity,
            'teachers' => $teachers
        ));

    }

    public function picturesAction(Request $request,$id){
         if($request->getMethod() == 'POST'){
            
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);
            $field['photoid']=$data['ids'];
            $this->getActivityService()->setActivitypictures($id,$field);
            $this->setFlashMessage('success', '专辑设置成功！');

            return $this->redirect($this->generateUrl('activity_manage_pictures',array('id' => $id))); 

        }

        $teacherMembers = $this->getActivityService()->getActivity($id);
        $photos=array();
        if(!empty($teacherMembers['photoid'])){
            $photos = $this->getPhotoService()->findPhotoByIds($teacherMembers['photoid']);    
        }

        $teachers = array();
        foreach ($photos as $member) {
            if (empty($member['id'])) {
                continue;
            }
            $teachers[] = array(
                'id' => $member['id'],
                'nickname' => $member['name'],
                'isVisible' => empty($member['isVisible']) ? true : false,
            );
        }
        return $this->render('TopxiaWebBundle:ActivityManage:pictures.html.twig', array(
            'course' => $this->getActivityService()->getActivity($id),
            'teachers' => $teachers
        ));

    }

    public function attachmentAction(Request $request,$id){
        $course = $this->getActivityService()->getActivity($id);
        return $this->render('TopxiaWebBundle:ActivityManage:attachment.html.twig', array(
            'course' => $course
        ));      
    }

    private function createActivityBaseForm($course)
    {
        $builder = $this->createNamedFormBuilder('activity', $course)
            ->add('title', 'text')
            ->add('subtitle', 'textarea')
            ->add('tagsid', 'tags')
            ->add('address','text')
            ->add('onlineAddress','text')
            ->add('form','text')
            ->add('strstartTime','text')
            ->add('strendTime','text')
            ->add('categoryid', 'default_category', array(
                'empty_value' => '请选择分类'
            )
        );

        return $builder->getForm();
    }


    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getActivityService(){
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    private function getPhotoService(){
        return $this->getServiceKernel()->createService('Photo.PhotoService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

}