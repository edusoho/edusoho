<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Endroid\QrCode\QrCode;


class CommonController extends BaseController
{

    public function qrcodeAction(Request $request)
    {
        $text = $request->get('text');
        
        $qrCode = new QrCode();
        $qrCode->setText($text);
        $qrCode->setSize(250);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qrcode.png"'
        );
        return new Response($img, 200, $headers);
    }

    public function generateLearnQrcodeAction(Request $request)
    {  
        $data = $request->query->all();
        if (isset($data['type'])){
            $route_type = $data['type'];
        }else {
            throw $this->createNotFoundException('必要参数错误，无法生成二维码');
        }
        
        $user = $this->getUserService()->getCurrentUser();
        if (!$user->isLogin()){
            if ($route_type == 'classroom-qrcode' && isset($data['classroomId'])) {
                $classroomId = (int)$data['classroomId'];
                $routePath = $this->generateUrl('classroom_show',array('id'=>$classroomId),true);
            }elseif ($route_type == 'course-qrcode' && isset($data['courseId'])) {
                $courseId = (int)$data['courseId'];
                $routePath = $this->generateUrl('course_show',array('id'=>$courseId),true);   
            }elseif ($route_type == 'lesson-qrcode' && isset($data['courseId']) && isset($data['lessonId'])) {
                $courseId = (int)$data['courseId'];
                $lessonId = (int)$data['lessonId'];
                $routePath = $this->generateUrl('course_learn',array('id' => $courseId),true)."#lesson/".$lessonId;
            }else {
                throw $this->createNotFoundException('参数错误，无法找到指定二维码');
            }
        }else {
            if ($route_type == 'classroom-qrcode' && isset($data['classroomId'])){
                $classroomId = (int)$data['classroomId'];
                $token = $this->getTokenService()->makeToken('classroom-qrcode',array('userId'=>$user['id'],'data' => array('classroomId'=>$classroomId), 'times' => 0, 'duration' => 3600));    
            }elseif ($route_type == 'course-qrcode' && isset($data['courseId'])) {
                $courseId = (int)$data['courseId'];
                $token = $this->getTokenService()->makeToken('course-qrcode',array('userId'=>$user['id'],'data' => array('courseId'=>$courseId), 'times' => 0, 'duration' => 3600));
            }elseif ($route_type == 'lesson-qrcode' && isset($data['lessonId']) && isset($data['courseId'])) {
                $courseId = (int)$data['courseId'];
                $lessonId = (int)$data['lessonId'];
                $token = $this->getTokenService()->makeToken('lesson-qrcode',array('userId'=>$user['id'],'data' => array('courseId'=>$courseId,'lessonId'=>$lessonId), 'times' => 0, 'duration' => 3600));
            }else {
                throw $this->createNotFoundException('参数错误，无法找到指定二维码');
            }
            $routePath = $this->generateUrl('common_parse_qrcode',array('token'=>$token['token']),true);
        }
        $url = $routePath.'?type='.$data['type']; 
        $response = array(
            'img' => $this->generateUrl('common_qrcode',array('text'=>$url),true)
        );
        return $this->createJsonResponse($response);
    }

    public function parseLearnCodeAction(Request $request,$token)
    {
        $route_type = $request->get('type');
        $token = $this->getTokenService()->verifyToken($route_type,$token);
        $pcUserId = $token['userId'];
        $user = $this->getUserService()->getUser($pcUserId);
        $currentUser = $this->getUserService()->getCurrentUser();
        if (!$currentUser->isLogin() || $currentUser['id']!==$pcUserId){
            $this->authenticateUser($user);
        }
        if (isset($route_type) && $route_type == 'course-qrcode' && !empty($token['data']['courseId'])){
            $courseId = $token['data']['courseId'];
            $gotoUrl = $this->generateUrl('course_show',array('id'=>$courseId),true);
        }elseif (isset($route_type) && $route_type == 'classroom-qrcode' && !empty($token['data']['classroomId'])) {
            $classroomId = $token['data']['classroomId'];
            $gotoUrl = $this->generateUrl('classroom_show',array('id'=>$classroomId),true);
        }elseif (isset($route_type) && $route_type == 'lesson-qrcode' && !empty($token['data']['courseId']) && !empty($token['data']['lessonId'])) {
            $courseId = $token['data']['courseId'];
            $lessonId = $token['data']['lessonId'];
            $gotoUrl = $this->generateUrl('course_learn',array('id' => $courseId),true)."#lesson/".$lessonId;
        }else {
            throw $this->createNotFoundException('参数错误，无法访问指定二维码');
        }
        return $this->redirect($gotoUrl);
    }

    public function crontabAction(Request $request)
    {
        $setting = $this->getSettingService()->get('magic', array());

        if (empty($setting['disable_web_crontab'])) {
            $this->getServiceKernel()->createService('Crontab.CrontabService')->scheduleJobs();
        }
        return $this->createJsonResponse(true);
    }
    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}