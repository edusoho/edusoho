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
            'roles'=>'ROLE_PARENT'
        );

        if(!empty($fields)){
            $conditions=$this->getConditionsByFields($conditions,$fields);
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


    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];
        $relations=$this->getUserService()->findUserRelationsByFromIdAndType($id,'family');
        $children=$this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'toId'));

        if ($request->getMethod() == 'POST') {
            $fields=$request->request->all();
            $profile = $this->getUserService()->updateUserProfile($user['id'], $fields);
            $this->getUserService()->changeTrueName($user['id'],$fields['truename']);
            $this->getUserService()->changeMobile($user['id'],$fields['mobile']);

            $this->getUserService()->deleteUserRelationsByFromIdAndType($id,'family');
            foreach ($fields['numbers'] as $number) {
                $child=$this->getUserService()->getUserByNumber($number);
                if(empty($child)){
                    throw $this->createNotFoundException('学号为'.$number.'的学生不存在！');
                }
                $userRelation['fromId']=$user['id'];
                $userRelation['toId']=$child['id'];
                $userRelation['type']='family';
                $userRelation['relation']=$fields['relation'];
                $userRelation['createdTime']=time();
                $this->getUserService()->addUserRelation($userRelation);
            }


            $this->getLogService()->info('user', 'edit', "管理员编辑用户资料 {$user['nickname']} (#{$user['id']})", $profile);
            return $this->redirect($this->generateUrl('settings'));
        }

        $fields=$this->getFields();
        return $this->render('TopxiaAdminBundle:Parent:edit-modal.html.twig', array(
            'user' => $user,
            'profile'=>$profile,
            'fields'=>$fields,
            'relation'=>$relations[0]['relation'],
            'children'=>$children
        ));
    }


    public function childNumberCheckAction(Request $request)
    {
        $childNumber = $request->query->get('value');
        $user=$this->getUserService()->getUserByNumber($childNumber);
        if(empty($user)){
            $response = array('success' => false, 'message' => '该学号学生并不存在!');
        }else{
            $response = array('success' => true, 'message' => '该学号可以使用');
        }
        return $this->createJsonResponse($response);
    }

    private function getFields()
    {
        $fields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        for($i=0;$i<count($fields);$i++){
            if(strstr($fields[$i]['fieldName'], "textField")) $fields[$i]['type']="text";
            if(strstr($fields[$i]['fieldName'], "varcharField")) $fields[$i]['type']="varchar";
            if(strstr($fields[$i]['fieldName'], "intField")) $fields[$i]['type']="int";
            if(strstr($fields[$i]['fieldName'], "floatField")) $fields[$i]['type']="float";
            if(strstr($fields[$i]['fieldName'], "dateField")) $fields[$i]['type']="date";
        }

        return $fields;
    }


    private function getConditionsByFields($conditions,$fields)
    {
        if(!empty($fields['class_id'])){
            $classStudents=$this->getClassesService()->findClassStudentMembers($fields['class_id']);
            $childIds=ArrayToolkit::column($classStudents, 'userId');
            if($fields['keywordType']=='childName'){
                $children=$this->getUserService()->searchUsers(array('truename'=>$fields['keyword']),array('createdTime', 'DESC'),0,PHP_INT_MAX);
                $childIds=array_intersect($childIds,ArrayToolkit::column($children, 'id'));
                $childIds=array_values($childIds);
            }else if($fields['keywordType']=='childNumber'){
                $child=$this->getUserService()->getUserByNumber($fields['keyword']);
                $childIds=in_array($child['id'], $childIds)?array($child['id']):array();
            }
            $relations=$this->getUserService()->findUserRelationsByToIdsAndType($childIds,'family');
            $ids=ArrayToolkit::column($relations, 'fromId');
            $conditions['ids']=empty($ids) ? array(0) : $ids;
        }else{
            $childIds=array();
            if($fields['keywordType']=='childName'){
                $children=$this->getUserService()->searchUsers(array('truename'=>$fields['keyword']),array('createdTime', 'DESC'),0,PHP_INT_MAX);
                $childIds=ArrayToolkit::column($children, 'id');
            }else if($fields['keywordType']=='childNumber'){
                $child=$this->getUserService()->getUserByNumber($fields['keyword']);
                $childIds=empty($child)?array():array($child['id']);
            }
            $relations=$this->getUserService()->findUserRelationsByToIdsAndType($childIds,'family');
            $ids=ArrayToolkit::column($relations, 'fromId');
            $conditions['ids']=empty($ids) ? array() : $ids;
        }
        if($fields['keywordType']=='truename' || $fields['keywordType']=='mobile'){
            $conditions['keywordType']=$fields['keywordType'];
            $conditions['keyword']=$fields['keyword'];
        }

        return $conditions;
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

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }
    
}