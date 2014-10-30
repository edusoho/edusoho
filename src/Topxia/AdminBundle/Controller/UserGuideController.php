<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\SimpleValidator;

class UserGuideController extends BaseController 
{
    public function indexAction(Request $request)
    {
        $guideSteps=$this->getSettingService()->get('guide_step');
        if(empty($guideSteps)){
            $guideSteps=array(
                array(
                    'content'=>'系统设置',
                    'completed'=>0
                ),
                array(
                    'content'=>'学校设置',
                    'completed'=>0
                ),
                array(
                    'content'=>'创建一个课程模板',
                    'completed'=>0
                ),
                array(
                    'content'=>'创建老师学生账号',
                    'completed'=>0
                ),
                array(
                    'content'=>'创建班级',
                    'completed'=>0
                ),
                array(
                    'content'=>'完成',
                    'completed'=>0
                )
            );
            $this->getSettingService()->set('guide_step',$guideSteps);
            return $this->render('TopxiaAdminBundle:UserGuide:main-modal.html.twig',array(
                'steps'=>$guideSteps
            ));
        }
        foreach ($guideSteps as $index=>$step) {
            if(empty($step['completed']) && $index<5){
                $step['index']=$index;
                return $this->createJsonResponse($step);
            }
        }
        return $this->createJsonResponse(false);
    }

    public function showAction(Request $request)
    {
        $guideSteps=$this->getSettingService()->get('guide_step');

        $index=$request->query->get('index');
        if(is_null($index)){
            return $this->createJsonResponse(false);
        }
        if(empty($guideSteps[$index])){
            return $this->createJsonResponse(false);
        }

        return $this->render('TopxiaAdminBundle:UserGuide:show-modal.html.twig',array(
            'guideSteps'=>$guideSteps,
            'step'=>$guideSteps[$index],
            'index'=>$index
        ));
    }

    public function completeAction(Request $request)
    {
        $guideSteps=$this->getSettingService()->get('guide_step');
        $index=$request->query->get('index');
        if(is_null($index)){
            return $this->createJsonResponse(false);
        }
        
        if(empty($guideSteps[$index])){
            return $this->createJsonResponse(false);
        }

        if(!$this->tryCompleteStep($index)){
            return $this->createJsonResponse(false);
        }

        $guideSteps[$index]['completed']=1;
        $this->getSettingService()->set('guide_step',$guideSteps);

        foreach ($guideSteps as $index=>$step) {
            if(empty($step['completed'])){
                $step['index']=$index;
                return $this->render('TopxiaAdminBundle:UserGuide:show-modal.html.twig',array(
                    'guideSteps'=>$guideSteps,
                    'step'=>$step,
                    'index'=>$index
                ));
            }
        }
    }

    private function tryCompleteStep($index)
    {
        if($index==0){
            return true;
        }else if($index==1){
            $school=$this->getSettingService()->get('school');
            return empty($school)?false:true;
        }else if($index==2){
            $courseCount=$this->getCourseService()->searchCourseCount(array());
            return $courseCount>0?true:false;
        }else if($index==3){
            $teacherCount=$this->getUserService()->searchUserCount(array('roles'=>'ROLE_TEACHER'));
            $studentCount=$this->getUserService()->searchUserCount(array('role'=>'ROLE_USER'));
            
            return ($teacherCount>1&&$studentCount>0)?true:false;
        }else if($index==4){
            $classCount=$this->getClassesService()->searchClassCount(array());
            return $classCount>0?true:false;
        }else{
            return false;
        }
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
}