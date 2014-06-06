<?php

namespace Topxia\Service\MyGroup\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\MyGroup\MyGroupService;
use Topxia\Common\ArrayToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\File\File;

class MyGroupServiceImpl extends BaseService implements MyGroupService {

//添加小组
    public function addGroup($group) {
//获得用户信息
        $user = $this->getCurrentUser();
        if (empty($group['title'])) {
            throw $this->createServiceException("小组名称为空！");
        }
//        if (empty($group['about'])) {
//            throw $this->createServiceException("小组介绍为空！");
//        }
//执行插入
        $id=$this->getMyGroupDao()->addGroup($group);
//写入日志
        $this->getLogService()->info('mygroup', 'create', " 创建了小组({$group['title']})");
        return $id;
    }
    public function getgroupowner_info($id){
        $memberid=$this->getMyGroupDao()->getownerId($id);
       return $this->getUserDao()->getUser($memberid);

     }
    public function searchGroup($condtion, $start, $limit,$sort) {
        return $this->getMyGroupDao()->searchGroup($condtion,$start,$limit,$sort);
    }
    public function getGroupinfo($id){
        if (empty($id)) {
            throw $this->createServiceException("找不到该小组！");
        }
          return $this->getMyGroupDao()->getGroupinfo($id);
    }
    public function isowner($id,$userid) {
         return $this->getMyGroupDao()->isowner($id,$userid);
     }
    public function changegroupbackgroundlogo($id, $filePath, $options){
        $groupinfo=$this->getGroupinfo($id);
        $pathinfo = pathinfo($filePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $grouplogoImage = $rawImage->copy();
        $grouplogoImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        
        $grouplogoImage->resize(new Box(1140, 254));
        $mediumFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_medium.{$pathinfo['extension']}";
        $grouplogoImage->save($mediumFilePath, array('quality' => 90));
        $mediumFileRecord = $this->getFileService()->uploadFile('user', new File($mediumFilePath));

        @unlink($filePath);
       //  print_r($groupinfo);die;
        $oldAvatars = array(
            'logo' => $groupinfo[0]['backgroundLogo'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $groupinfo[0]['backgroundLogo']) : null,
         );

        array_map(function($oldAvatar){
            if (!empty($oldAvatar)) {
                @unlink($oldAvatar);
            }
        }, $oldAvatars);
        return  $this->getMyGroupDao()->updatgroupinfo($id, array(
            'backgroundlogo' => $mediumFileRecord['uri'],
        ));
         }
    public function changegrouplogo($id, $filePath, $options){
        $groupinfo=$this->getGroupinfo($id);
        $pathinfo = pathinfo($filePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $grouplogoImage = $rawImage->copy();
        $grouplogoImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        
        $grouplogoImage->resize(new Box(120, 120));
        $mediumFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_medium.{$pathinfo['extension']}";
        $grouplogoImage->save($mediumFilePath, array('quality' => 90));
        $mediumFileRecord = $this->getFileService()->uploadFile('user', new File($mediumFilePath));

        @unlink($filePath);
       //  print_r($groupinfo);die;
        $oldAvatars = array(
            'logo' => $groupinfo[0]['logo'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $groupinfo[0]['logo']) : null,
         );

        array_map(function($oldAvatar){
            if (!empty($oldAvatar)) {
                @unlink($oldAvatar);
            }
        }, $oldAvatars);
        return  $this->getMyGroupDao()->updatgroupinfo($id, array(
            'logo' => $mediumFileRecord['uri'],
        ));

     }
     public function updategroupinfo($id,$group){
        if (empty($group['title'])) {
            throw $this->createServiceException("小组名称为空！");
        }
        $status=$this->getMyGroupDao()->updategroupinfo($id,$group);
        //写入日志
        $this->getLogService()->info('updategroupinfo', 'create', " 修改了小组({$group['title']})");
        return $status;

     }
     public function getAllgroupinfo($condtion,$sort,$start,$limit){

        return $this->getMyGroupDao()->getAllgroupinfo($condtion,$sort,$start,$limit);
     }
     public function getAllgroupCount($condtion){
   
        return $this->getMyGroupDao()->getAllgroupCount($condtion);
     }
    public function openGroup($id){
         return $this->getMyGroupDao()->openGroup($id);
    }
    public function closeGroup($id){
         return $this->getMyGroupDao()->closeGroup($id);
    }
    private function getLogService() {
        return $this->createService('System.LogService');
    }

    private function getMyGroupDao() {
        return $this->createDao('MyGroup.MyGroupDao');
    }
    private function getUserDao() {
        return $this->createDao('User.UserDao');
    }
     private function getFileService()
    {
        return $this->createService('Content.FileService');
    }

}
