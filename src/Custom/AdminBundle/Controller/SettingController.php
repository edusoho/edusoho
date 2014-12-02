<?php 
namespace Custom\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Topxia\Service\Util\LiveClientFactory;
class SettingController extends BaseController
{
    public function courseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', array());
        
        $client = LiveClientFactory::createClient();
        $capacity = $client->getCapacity();

        $default = array(
            'welcome_message_enabled' => '0',
            'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
            'buy_fill_userinfo' => '0',
            'teacher_modify_price' => '1',
            'teacher_manage_student' => '0',
            'teacher_export_student'=>'0',
            'student_download_media' => '0',
            'free_course_nologin_view' => '1',
            'relatedCourses' => '0',
            'allowAnonymousPreview' => '1',
            'live_course_enabled' => '0',
            'userinfoFields'=>array(),
            "userinfoFieldNameArray"=>array(),
            "copy_enabled"=>'0',
            "picturePreview_enabled"=>'0',
            'relatedArticles'=>'0'
        );

        $this->getSettingService()->set('course', $courseSetting);
        $courseSetting = array_merge($default, $courseSetting);

        if ($request->getMethod() == 'POST') {
            $courseSetting = $request->request->all();

            if(!isset($courseSetting['userinfoFields']))$courseSetting['userinfoFields']=array();
            if(!isset($courseSetting['userinfoFieldNameArray']))$courseSetting['userinfoFieldNameArray']=array();

            $courseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

            $this->getSettingService()->set('course', $courseSetting);
            $this->getLogService()->info('system', 'update_settings', "更新课程设置", $courseSetting);
            $this->setFlashMessage('success','课程设置已保存！');
        }

        $courseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];
        
        $userFields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        if($courseSetting['userinfoFieldNameArray']){
            foreach ($userFields as $key => $fieldValue) {
                if(!in_array($fieldValue['fieldName'], $courseSetting['userinfoFieldNameArray'])){
                    $courseSetting['userinfoFieldNameArray'][]=$fieldValue['fieldName'];
                }
            }
         
        }

        return $this->render('CustomAdminBundle:System:course-setting.html.twig', array(
            'courseSetting' => $courseSetting,
            'userFields'=>$userFields,
        ));
    }
     protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
   protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }
}