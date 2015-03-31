<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class TeacherController extends BaseController
{
    /**
     * 获取所有标签，以JSONM的方式返回数据
     * 
     * @return JSONM Response
     */
    public function searchAction(Request $request)
    {   
        $gradeTagId = $request->query->get('gradeTagId');
        $subjectTagId = $request->query->get('subjectTagId');

         $conditions = array(
            'roles'=>'ROLE_TEACHER',
            'locked'=>0
        );

        $gradeTag = "";
        if(!empty($gradeTagId)){
            $conditions['gradeTag'] = $gradeTagId ;
            $gradeTag = $this->getTagService()->getTag($gradeTagId);

        }
        $subjectTag = "";
        if(!empty($subjectTagId)){
            $conditions['subjectTag'] = $subjectTagId ;
            $subjectTag = $this->getTagService()->getTag($subjectTagId);
        }
  
        // var_dump($conditions);
        // // var_dump($gradeTag);
        // // var_dump($subjectTag);
        // exit();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCustomUserService()->searchUserCount($conditions),
            20
        );

        $teachers = $this->getCustomUserService()->searchUsers(
            $conditions,
            array('promotedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

      
        $profiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($teachers, 'id'));

        return $this->render('TopxiaWebBundle:Teacher:index.html.twig', array(
            'teachers' => $teachers ,
            'profiles' => $profiles,
            'paginator' => $paginator,
            'gradeTag' => $gradeTag,
            'subjectTag' => $subjectTag
        ));
       
    }

 

  

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.TagTeacherService');
    }

    protected function getCustomUserService()
    {
        return $this->getServiceKernel()->createService('Custom:User.UserService');
    }

}