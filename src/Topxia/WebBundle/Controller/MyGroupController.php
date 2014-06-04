<?php

namespace Topxia\WebBundle\Controller;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\Request;

class MyGroupController extends BaseController {

    public function IndexAction() {


        $activegroup = $this->getMyGroupService()->searchGroup(null, 0, 9, 'postNum desc');
        $user = $this->getCurrentUser();
        if ($user['id']) {
            $mycreatedGroup = $this->getMyGroupService()->searchGroup(array('ownerId'=>$user['id']), 0, 8, 'createdTime desc');
            $myjionGroup =  $this->getGroupMemberService()->searchjoinGroup(array('ownerId'=>$user['id']), 0, 8, 'createdTime desc');
            
        } else {
            $mycreatedGroup = array();
            $myjionGroup = array();
        }
        return $this->render("TopxiaWebBundle:MyGroup:index.html.twig", array(
                    'activegroup' => $activegroup,
                    'mycreatedGroup' => $mycreatedGroup,
                    'myjionGroup' => $myjionGroup,
        ));
    }

    public function Mygroup_addAction(Request $request) {
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $mygroup = $request->request->all();
            //print_r($profile['profile']);
            // $response=new Response($profile['profile']['grouptitle']);
            //return $response;
            $group = array(
                'title' => $mygroup['group']['grouptitle'],
                'about' => $mygroup['group']['about'],
                'ownerId' => $user['id'],
                'memberNum' => 1,
                'createdTime' => time(),
            );
            $id = $this->getMyGroupService()->addGroup($group);

            if ($id) {
                $this->setFlashMessage('success', '创建成功。<a href="groupindex/' . $id . '" class="alert-link">赶紧去看看吧！</a>');
            } else {
                $this->setFlashMessage('danger', '创建失败。');
            }

            return $this->redirect($this->generateUrl('mygroup_add'));
        }


        return $this->render("TopxiaWebBundle:MyGroup:groupadd.html.twig");
    }

    public function GroupindexAction(Request $request,$id) {
      
        $groupinfo = $this->getMyGroupService()->getgroupinfo($id);
        $groupmember_recentlyinfo=$this->getGroupMemberService()->getgroupmember_recentlyinfo($id);

        return $this->render("TopxiaWebBundle:MyGroup:groupindex.html.twig", array(
                    'groupinfo' => $groupinfo[0],
                    'is_groupmember' => $this->is_groupmember($id),
                    'groupmember_recentlyinfo'=>$groupmember_recentlyinfo,
        ));
    }
     public function GroupmemberAction($id) {
       
        $groupinfo = $this->getMyGroupService()->getgroupinfo($id);
        $groupmember_info=$this->getGroupMemberService()->getgroupmember_info($id);
        $owner_info=$this->getMyGroupService()->getgroupowner_info($id);
        return $this->render("TopxiaWebBundle:MyGroup:groupmember.html.twig", array(
                    'groupinfo' => $groupinfo[0],
                    'is_groupmember' => $this->is_groupmember($id),
                    'groupmember_info'=>$groupmember_info,
                    'owner_info'=>$owner_info,
        ));
    }
    public function SetgrouplogoAction(Request $request,$id){
        $user=$this->getCurrentUser();
         //是否是创建者
            if (!$this->getMyGroupService()->isowner($id, $user['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者!');
            }
        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                     $this->setFlashMessage('danger', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                     return $this->render('TopxiaWebBundle:MyGroup:setgrouplogo.html.twig',array(
                     'form' => $form->createView(),));
                }

                $filenamePrefix = "user_{$user['id']}_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;
                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
                $file = $file->move($directory, $filename);

                $fileName = str_replace('.', '!', $file->getFilename());  
                return $this->redirect($this->generateUrl('setgrouplogo_crop', array(
                    'file' => $fileName,
                    'id'=>$id,
                    )
                ));
            }
        }
        $groupinfo=$this->getMyGroupService()->getgroupinfo($id);
        return $this->render('TopxiaWebBundle:MyGroup:setgrouplogo.html.twig',array(
             'form' => $form->createView(),
             'id'=>$id,
             'logo'=>$groupinfo[0]['logo'],));
    }
    public function Setgrouplogo_cropAction(Request $request,$file,$id){

        $currentUser = $this->getCurrentUser();
         //是否是创建者
            if (!$this->getMyGroupService()->isowner($id, $currentUser['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者!');
            }
        $filename = $file;
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            
            $options = $request->request->all();
            $this->getMyGroupService()->changegrouplogo($id, $pictureFilePath, $options);
             $this->setFlashMessage('success', '修改成功!');
        return $this->forward('TopxiaWebBundle:MyGroup:Setgrouplogo', array(
                    'id' => $id,));
        }
        try {

            $imagine = new Imagine(); 
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {          
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = 'tmp/' . $filename;
        return $this->render('TopxiaWebBundle:MyGroup:setgrouplogo_crop.html.twig',array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,));

    }
    public function is_groupmember($id){
       $user = $this->getCurrentUser();
        if ($user['id']) {


            //是否是创建者
            if ($this->getMyGroupService()->isowner($id, $user['id'])) {
                $is_groupmember = 2;
            }
            //是否是小组成员
            elseif ($this->getGroupMemberService()->ismember($id, $user['id'])) {
                $is_groupmember = 1;
            }
            //都不是
            else {
                $is_groupmember = 0;
            }
        } else {
            $is_groupmember = 0;
        }
        return $is_groupmember;
    }
    public function GroupjoinAction($id, $title) {
        $joinid = $this->getGroupMemberService()->joinGroup($id, $title);
        if ($joinid) {
            $this->setFlashMessage('success', '加入成功。');
        } else {
            $this->setFlashMessage('danger', '加入失败。您已经加入了该小组');
        }
        return $this->forward('TopxiaWebBundle:MyGroup:Groupindex', array(
                    'id' => $id,));
    }

    public function GroupexitAction($id, $title) {
        $exitid = $this->getGroupMemberService()->exitGroup($id, $title);
        if ($exitid) {
            $this->setFlashMessage('success', '退出成功。');
        } else {
            $this->setFlashMessage('danger', '退出失败。您已经退出了该小组');
        }
        return $this->forward('TopxiaWebBundle:MyGroup:Groupindex', array(
                    'id' => $id,));
    }
    public function groupinfo_editAction(Request $request,$id){
        $currentUser = $this->getCurrentUser();
         //是否是创建者
            if (!$this->getMyGroupService()->isowner($id, $currentUser['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者!');
            }
        $groupinfo=$request->request->all();
        $group=array();
        if($groupinfo){
              $group=array(
            'title'=>$groupinfo['group']['grouptitle'],
            'about'=>$groupinfo['group']['about']); 
        }        
     
    if($this->getMyGroupService()->updategroupinfo($id,$group)){
           $this->setFlashMessage('success', '修改成功。');
       }else{
        $this->setFlashMessage('danger', '修改失败。');
       }
    return $this->forward('TopxiaWebBundle:MyGroup:Groupindex', array(
                    'id' => $id,));
         
    }

    private function getMyGroupService() {

        return $this->getServiceKernel()->createService('MyGroup.MyGroupService');
    }

    private function getGroupMemberService() {

        return $this->getServiceKernel()->createService('MyGroup.GroupMemberService');
    }

}
