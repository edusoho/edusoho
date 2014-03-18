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
        $activity = $this->getActivityService()->getActivity($id);
        $form = $this->createActivityBaseForm($activity);
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                $activityBaseInfo = $form->getData();
                $this->getActivityService()->updateActivity($id, $activityBaseInfo);
                $this->setFlashMessage('success', '活动基本信息已保存！');
                return $this->redirect($this->generateUrl('activity_manage_base',array('id' => $id))); 
            }
        }
        $activity['strstartTime']=$activity['startTime'];
        $activity['strendTime']=$activity['endTime'];
        $newActivity=$activity;
        return $this->render('TopxiaWebBundle:ActivityManage:base.html.twig', array(
            'activity' => $newActivity,
            'form' => $form->createView(),
        ));
    }


    public function outlineAction(Request $request, $id)
    {
        
        $activity = $this->getActivityService()->getActivity($id);

        if($request->getMethod() == 'POST'){
            $outline = $request->request->all();
           
            $this->getActivityService()->updateActivity($id, $outline);
            $this->setFlashMessage('success', '课程简要信息已保存！');

            return $this->redirect($this->generateUrl('activity_manage_outline',array('id' => $id))); 
        }

        return $this->render('TopxiaWebBundle:ActivityManage:outline.html.twig', array(
            'activity' => $activity
        ));
    }

    public function detailAction(Request $request, $id)
    {
        
        $activity = $this->getActivityService()->getActivity($id);

        if($request->getMethod() == 'POST'){
            $detail = $request->request->all();
           

            $this->getActivityService()->updateActivity($id, $detail);
            $this->setFlashMessage('success', '课程详细信息已保存！');

            return $this->redirect($this->generateUrl('activity_manage_detail',array('id' => $id))); 
        }

        return $this->render('TopxiaWebBundle:ActivityManage:detail.html.twig', array(
            'activity' => $activity
        ));
    }


    public function summaryAction(Request $request, $id)
    {
        
        $activity = $this->getActivityService()->getActivity($id);

        if($request->getMethod() == 'POST'){
            $detail = $request->request->all();
           

            $this->getActivityService()->updateActivity($id, $detail);
            $this->setFlashMessage('success', '课程详细信息已保存！');

            return $this->redirect($this->generateUrl('activity_manage_summary',array('id' => $id))); 
        }

        return $this->render('TopxiaWebBundle:ActivityManage:summary.html.twig', array(
            'activity' => $activity
        ));
    }

    public function pictureAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $activity = $this->getActivityService()->getActivity($id);
        if($request->getMethod() == 'POST'){
            $file = $request->files->get('picture');

            $filenamePrefix = "activity_{$activity['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $filename = $filenamePrefix . $hash . '.' . $file->getClientOriginalExtension();

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';

            $file = $file->move($directory, $filename);

            return $this->redirect($this->generateUrl('activity_manage_picture_crop', array(
                'id' => $activity['id'],
                'file' => $file->getFilename())
            ));
        }

        return $this->render('TopxiaWebBundle:ActivityManage:picture.html.twig', array(
            'activity' => $activity,
        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $activity = $this->getActivityService()->getActivity($id);

        //@todo 文件名的过滤
        $filename = $request->query->get('file');
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $this->getActivityService()->changeActivityPicture($activity['id'], $pictureFilePath, $c);
            return $this->redirect($this->generateUrl('activity_manage_picture', array('id' => $activity['id'])));
        }

        $imagine = new Imagine();
        $image = $imagine->open($pictureFilePath);

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(300)->heighten(300);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return $this->render('TopxiaWebBundle:ActivityManage:picture-crop.html.twig', array(
            'activity' => $activity,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function priceAction(Request $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);

        $form = $this->createActivityPriceForm($activity);
        
        if($request->getMethod() == 'POST'){
            $form->bind($request);

            $activityBaseInfo = $form->getData();     

            if($form->isValid()){

                $activityBaseInfo = $form->getData();
                if(isset($activityBaseInfo['startTime']))
                {
                    unset($activityBaseInfo['startTime']);
                }
                if(isset($activityBaseInfo['endTime']))
                {
                    unset($activityBaseInfo['endTime']);
                }
                if(isset($activityBaseInfo['recommentTime']))
                {
                    unset($activityBaseInfo['recommentTime']);
                }
                $this->getActivityService()->updateActivity($id, $activityBaseInfo);
                $this->setFlashMessage('success', '价格已保存！');
              
            }
        }

       
        return $this->render('TopxiaWebBundle:ActivityManage:price.html.twig', array(
            'activity' => $activity,
            'form' => $form->createView(),
        ));
        
    }

  
    public function teachersAction(Request $request, $id)
    {
        if($request->getMethod() == 'POST'){
            
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $field['experters']=$data['ids'];
            $this->getActivityService()->setActivityTeachers($id,$field);
            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('activity_manage_teachers',array('id' => $id))); 

        }

        $teacherMembers = $this->getActivityService()->getActivity($id);
        $users = $this->getUserService()->findUsersByIds($teacherMembers['experters']);

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
            'activity' => $this->getActivityService()->getActivity($id),
            'teachers' => $teachers
        ));
    }

    public function membersAction(Request $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);

        $paginator = new Paginator(
            $request,
            $activity['studentNum'],
            20
        );

        $students = $this->getActivityService()->findActivityStudents(
            $activity['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        
        return $this->render('TopxiaWebBundle:ActivityMemberManage:index.html.twig', array(
            'activity' => $activity,
            'students' => $students,
            'users'=>$users,
         
            'paginator' => $paginator
            
        ));


    }


    public function exportCsvAction (Request $request, $id)
    {   

        $activity = $this->getActivityService()->tryAdminActivity($id);

        $courseMembers = $this->getActivityService()->findActivityStudents($activity['id'],0,10000);

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);

       
        $str = "用户名,报名时间,姓名,Email,公司,职位,头衔,电话,微信号,QQ号"."\r\n";

        $students = array_map(function($user,$courseMember,$profile){
            $member['nickname']   = $user['nickname'];
            $member['joinedTime'] = date('Y-n-d H:i:s', $courseMember['createdTime']);
      
            $member['truename'] = $profile['truename'] ? $profile['truename'] : "-";
            $member['email'] = $user['email'] ? $user['email'] : "-";
            $member['company'] = $profile['company'] ? $profile['company'] : "-";
            
            $member['job'] = $profile['job'] ? $profile['job'] : "-";

            $member['title'] = $user['title'] ? $user['title'] : "-";
            $member['mobile'] = $profile['mobile'] ? $profile['mobile'] : "-";
            $member['weixin'] = $profile['weixin'] ? $profile['weixin'] : "-";
            $member['qq'] = $profile['qq'] ? $profile['qq'] : "-";
            return implode(',',$member);
        }, $users,$courseMembers,$profiles);
        $str .= implode("\r\n",$students);
        $str = chr(239) . chr(187) . chr(191) . $str;

        $filename = sprintf("activity-%s-members-(%s).csv", $activity['id'], date('Y-n-d'));

        $userId = $this->getCurrentUser()->id;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }



    public function publishAction(Request $request, $id)
    {
        $this->getActivityService()->publishActivity($id);
        return $this->createJsonResponse(true);
    }

    public function teachersMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(array('nicknameLike'=>$likeString, 'roles'=> 'ROLE_TEACHER'),array('createdTime', 'DESC'),0, 10);

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
            'activity' => $activity,
            'teachers' => $teachers
        ));

    }

    public function picturesAction(Request $request,$id){
         if($request->getMethod() == 'POST'){
            
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);
            $field['photoId']=$data['ids'];
            $this->getActivityService()->setActivitypictures($id,$field);
            $this->setFlashMessage('success', '专辑设置成功！');

            return $this->redirect($this->generateUrl('activity_manage_pictures',array('id' => $id))); 

        }

        $teacherMembers = $this->getActivityService()->getActivity($id);
        $photos=array();
        if(!empty($teacherMembers['photoId'])){
            $photos = $this->getPhotoService()->findPhotoByIds($teacherMembers['photoId']);    
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
            'activity' => $this->getActivityService()->getActivity($id),
            'teachers' => $teachers
        ));

    }

    public function attachmentAction(Request $request,$id){
        $course = $this->getActivityService()->getActivity($id);
        return $this->render('TopxiaWebBundle:ActivityManage:attachment.html.twig', array(
            'course' => $course
        ));      
    }

    private function createActivityBaseForm($activity)
    {
        $builder = $this->createNamedFormBuilder('activity', $activity)
            ->add('title', 'text')
            ->add('subtitle', 'textarea')
            ->add('actType', 'act_type',array('multiple'=>false,'expanded'=>false,'required'=>true))
            ->add('city', 'city',array('multiple'=>false,'expanded'=>false,'required'=>true))         
            ->add('address','text')
            ->add('tags', 'tags')
            ->add('onlineAddress','text')
            ->add('form','text')
            ->add('duration','text')
            ->add('strstartTime','text')
            ->add('strendTime','text')
            ->add('categoryId', 'default_category', array(
                'empty_value' => '请选择分类'
                )
            );
        return $builder->getForm();
    }


    private function createActivityPriceForm($activity)
    {
        return $this->createNamedFormBuilder('activity', $activity)
            ->add('price', 'text',array('required'=>true))
            ->add('onlinePrice', 'text',array('required'=>true))
            ->add('payment', 'choice',array('choices'=>array('线上支付'=>'线上支付','线下支付'=>'线下支付'),'multiple'=>false,'expanded'=>false,'required'=>true))

            ->getForm();
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