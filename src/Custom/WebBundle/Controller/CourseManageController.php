<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseManageController extends BaseController
{
    public function priceAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $canModifyPrice = true;
        $teacherModifyPrice = $this->setting('course.teacher_modify_price', true);
        if ($this->setting('vip.enabled')) {
            $levels = $this->getLevelService()->findEnabledLevels();
        } else {
            $levels = array();
        }
        if (empty($teacherModifyPrice)) {
            if (!$this->getCurrentUser()->isAdmin()) {
                $canModifyPrice = false;
                goto response;
            }
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            if(isset($fields['freeStartTime'])){
                $fields['freeStartTime'] = strtotime($fields['freeStartTime']);
                $fields['freeEndTime'] = strtotime($fields['freeEndTime']);
            }
            
            $course = $this->getCourseService()->updateCourse($id, $fields);
            $this->setFlashMessage('success', '课程价格已经修改成功!');
        }



        response:
        return $this->render('CustomWebBundle:CourseManage:price.html.twig', array(
            'course' => $course,
            'canModifyPrice' => $canModifyPrice,
            'levels' => $this->makeLevelChoices($levels),
        ));
    }

    public function setPriceAction($id)
    {   
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '只允许管理员设置课时价格!');
        }

        
        return $this->render('CustomWebBundle:CourseManage:set-price.html.twig',array(
            ));
    }

    private function makeLevelChoices($levels)
    {
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }
        return $choices;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}