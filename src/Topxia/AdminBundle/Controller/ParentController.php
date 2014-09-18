<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ParentController extends BaseController 
{
    public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'roles'=>'ROLE_PARENT',
            'truename'=>'',
            'number'=>''
        );

        if(!empty($fields)){
            $conditions =array_merge($conditions,$fields);
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
        
        $userRelations=$this->getUserService()->findUserRelationsByFromIdsAndType(ArrayToolkit::column($users, 'id'),'family');
        
        $relations=array();
        foreach ($userRelations as $useRelation) {
            $relations=array_merge($relations,$useRelation);
        }
        $children=$this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'toId'));
        $classMembers=$this->getClassesService()->findClassMembersByUserIds(ArrayToolkit::column($relations, 'toId'));
        $classes=$this->getClassesService()->findClassesByIds(ArrayToolkit::column($classMembers, 'classId'));
        $classMembers=ArrayToolkit::index($classMembers, 'userId');
        $classes=ArrayToolkit::index($classes, 'id');
        return $this->render('TopxiaAdminBundle:Parent:index.html.twig', array(
            'users' => $users,
            'userRelations'=> $userRelations,
            'children' => $children,
            'classMembers' =>$classMembers,
            'classes' =>$classes,
            'paginator' => $paginator
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $userData['mobile'] = $formData['mobile'];
            $userData['email'] = $formData['email'];
            $userData['truename'] = $formData['truename'];
            $userData['password'] = $formData['password'];
            $userData['createdIp'] = $request->getClientIp();
            $userData['number'] = 'p'.$formData['mobile'];
            $userData['nickname'] = 'p'.$formData['mobile'];

            
            $user = $this->getAuthService()->register($userData);
            $this->get('session')->set('registed_email', $user['email']);
                
            $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER','ROLE_PARENT'));

            foreach ($formData['numbers'] as $number) {
                $child=$this->getUserService()->getUserByNumber($number);
                if(empty($child)){
                    throw $this->createNotFoundException('学号为'.$number.'的学生不存在！');
                }
                $userRelation['fromId']=$user['id'];
                $userRelation['toId']=$child['id'];
                $userRelation['type']='family';
                $userRelation['relation']=$formData['relation'];
                $userRelation['createdTime']=time();
                $this->getUserService()->addUserRelation($userRelation);
            }

            $this->getLogService()->info('user', 'add', "管理员添加新用户 {$user['truename']} ({$user['id']})");

            return $this->redirect($this->generateUrl('admin_user'));
        }
        return $this->render('TopxiaAdminBundle:Parent:create-modal.html.twig');
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }
    
}