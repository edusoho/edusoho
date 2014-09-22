<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class StudentController extends BaseController 
{
    public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'role'=>'ROLE_USER',
            'truename'=>'',
            'number'=>''
        );

        if(!empty($fields)){
            $conditions['truename']=$fields['search_truename'];
            $conditions['number']=$fields['search_number'];
            //$conditions =array_merge($conditions,$fields);
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userMembers=$this->getClassesService()->findClassMembersByUserIds(ArrayToolkit::column($users, 'id'));
        $classes=$this->getClassesService()->findClassesByIds(ArrayToolkit::column($userMembers, 'classId'));
        $userMembers=ArrayToolkit::index($userMembers, 'userId');
        $classes=ArrayToolkit::index($classes, 'id');
        return $this->render('TopxiaAdminBundle:Student:index.html.twig', array(
            'users' => $users,
            'userMembers' =>$userMembers,
            'classes' =>$classes,
            'paginator' => $paginator
        ));
    }

    public function classListAction(Request $request){
        $schoolSetting=$this->getSettingService()->get('school');

        $schools=array();
        if(isset($schoolSetting['primarySchool'])) {
            if($schoolSetting['primaryYear'] == '6'){
                $schools['primarySchool']=array(
                    'name'=>'小学',
                    'grades'=>array(
                        '1'=>'一年级',
                        '2'=>'二年级',
                        '3'=>'三年级',
                        '4'=>'四年级',
                        '5'=>'五年级',
                        '6'=>'六年级'
                    )
                );
            }else{
                $schools['primarySchool']=array(
                    'name'=>'小学',
                    'grades'=>array(
                        '1'=>'一年级',
                        '2'=>'二年级',
                        '3'=>'三年级',
                        '4'=>'四年级',
                        '5'=>'五年级'
                    )
                );
            }
        }

        if(isset($schoolSetting['middleSchool'])) {
            $schools['middleSchool']=array(
                'name'=>'初中',
                'grades'=>array(
                    '7'=>'一年级',
                    '8'=>'二年级',
                    '9'=>'三年级'
                )
            );
        }

        if(isset($schoolSetting['highSchool'])) {
            $schools['middleSchool']=array(
                'name'=>'高中',
                'grades'=>array(
                    '10'=>'一年级',
                    '11'=>'二年级',
                    '12'=>'三年级'
                )
            );
        }
        $classes = $this->getClassesService()->searchClasses(
            array(),
            array(),
            0,
            PHP_INT_MAX
        );

        
        return $this->render('TopxiaAdminBundle:Student:class-list-modal.html.twig',array(
            'schools'=>$schools,
            'classes'=>$classes
        ));
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
    
}