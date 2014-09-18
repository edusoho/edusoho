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
        $userMembers=$this->getClassesService()->findClassMembersByUserIds(ArrayToolkit::column($users, 'id'));
        $classes=$this->getClassesService()->findClassesByIds(ArrayToolkit::column($userMembers, 'classId'));
        $userMembers=ArrayToolkit::index($userMembers, 'userId');
        $classes=ArrayToolkit::index($classes, 'id');
        return $this->render('TopxiaAdminBundle:Parent:index.html.twig', array(
            'users' => $users,
            'userMembers' =>$userMembers,
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
    
}