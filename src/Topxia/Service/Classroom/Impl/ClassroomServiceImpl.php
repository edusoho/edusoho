<?php

namespace Topxia\Service\Classroom\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Classroom\ClassroomService;
use Topxia\Common\ArrayToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\File\File;


class ClassroomServiceImpl extends BaseService implements ClassroomService 
{
    public function getClassroom($id)
    {   
        $classroom=$this->getClassroomDao()->getClassroom($id);

        $classroom['teacherIds']=$classroom['teacherIds'] ? json_decode($classroom['teacherIds'],true) : array();

        return $classroom;
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit)
    {
        return $this->getClassroomDao()->searchClassrooms($conditions,$orderBy,$start,$limit);
    }

    public function searchClassroomsCount($conditions)
    {
         $count= $this->getClassroomDao()->searchClassroomsCount($conditions);
         return $count;
    }

    public function addClassroom($classroom)
    {   
        $title=trim($classroom['title']);
        if (empty($title)) {
            throw $this->createServiceException("班级名称不能为空！");
        }

        $classroom['createdTime']=time();
        $classroom = $this->getClassroomDao()->addClassroom($classroom);

        return $classroom;
    }

    public function updateClassroom($id,$fields)
    {   
        $classroom=$this->getClassroomDao()->updateClassroom($id,$fields);

        $classroom['teacherIds']=$classroom['teacherIds'] ? json_decode($classroom['teacherIds'],true) : array();

        return $classroom;
    }

    public function deleteClassroom($id)
    {
        $classroom = $this->getClassroom($id);
            
        if(empty($classroom)){
            throw $this->createServiceException("班级不存在，操作失败。");
        }

        $this->getClassroomDao()->deleteClassroom($id);
        $this->getLogService()->info('Classroom', 'delete', "班级#{$id}永久删除");
        return true;
    }

    public function updateClassroomTeachers($id)
    {
        $courses=$this->getAllCourses($id);

        $classroom=$this->getClassroom($id);
        
        $teacherIds=$classroom['teacherIds'] ? : array();
        $ids=array();
        foreach ($teacherIds as $key => $value) {
            
            $course=$this->getCourseByClassroomIdAndCourseId($id,$value);

            if(empty($course)){

                unset($teacherIds[$key]);
            }
        }

        foreach ($courses as $key => $value) {
            
            $course=$this->getCourseService()->getCourse($value['courseId']);

            $teacherIds=array_merge($teacherIds,$course['teacherIds']);

        }

        $teacherIds=array_unique($teacherIds);

        foreach ($teacherIds as $key => $value) {
            
            $ids[]=$value;
        }
        
        $teacherIds=json_encode($ids);

        $this->updateClassroom($id,array('teacherIds'=>$teacherIds));
        
    }

    public function publishClassroom($id)
    {    
        $this->updateClassroom($id,array("status"=>"published"));
    }

    public function closeClassroom($id)
    {    
        $this->updateClassroom($id,array("status"=>"closed"));
    }

    public function changePicture ($id, $filePath, array $options)
    {
        $classroom = $this->getClassroomDao()->getClassroom($id);
        if (empty($classroom)) {
            throw $this->createServiceException('班级不存在，图标更新失败！');
        }

        $pathinfo = pathinfo($filePath);
        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(480, 270));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));
        $largeFileRecord = $this->getFileService()->uploadFile('default', new File($largeFilePath));

        $largeImage->resize(new Box(304, 171));
        $middleFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_middle.{$pathinfo['extension']}";
        $largeImage->save($middleFilePath, array('quality' => 90));
        $middleFileRecord = $this->getFileService()->uploadFile('default', new File($middleFilePath));

        $largeImage->resize(new Box(96, 54));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $largeImage->save($smallFilePath, array('quality' => 90));
        $smallFileRecord = $this->getFileService()->uploadFile('default', new File($smallFilePath));

        $fields = array(
            'smallPicture' => $smallFileRecord['uri'],
            'middlePicture' => $middleFileRecord['uri'],
            'largePicture' => $largeFileRecord['uri'],
        );

        @unlink($filePath);

        $oldPictures = array(
            'smallPicture' => $classroom['smallPicture'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $classroom['smallPicture']) : null,
            'middlePicture' => $classroom['middlePicture'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $classroom['middlePicture']) : null,
            'largePicture' => $classroom['largePicture'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $classroom['largePicture']) : null
        );


        array_map(function($oldPicture){
            if (!empty($oldPicture)){
                @unlink($oldPicture);
            }
        }, $oldPictures);

        $this->getLogService()->info('classroom', 'update_picture', "更新课程《{$classroom['title']}》(#{$classroom['id']})图片", $fields);
        
        return $this->updateClassroom($id,$fields);
    }

    public function addCourse($id,$courseId)
    {
        $classroomCourse=array(
            'classroomId'=>$id,
            'courseId'=>$courseId);

        $this->getClassroomCourseDao()->addCourse($classroomCourse);
    }

    public function getCourseByClassroomIdAndCourseId($id,$courseId)
    {
        return $this->getClassroomCourseDao()->getCourseByClassroomIdAndCourseId($id,$courseId);
    }

    public function getAllCourses($classroomId)
    {   
        $conditions=array('classroomId'=>$classroomId);

        $courses=$this->getClassroomCourseDao()->searchCourses($conditions,array('id','asc'),0,9999);

        return $courses;
    }

    public function updateCourses($classroomId,array $courseIds)
    {
        $this->getClassroomCourseDao()->deleteCoursesByClassroomId($classroomId);

        foreach ($courseIds as $key => $value) {
            
            $this->addCourse($classroomId,$value);
        }
    }

    public function findCoursesByIds(array $ids)
    {
        return ArrayToolkit::index( $this->getClassroomCourseDao()->findCoursesByIds($ids), 'id');
    }

    public function searchMemberCount($conditions)
    {   
        $conditions = $this->_prepareClassroomConditions($conditions);
        return $this->getClassroomMemberDao()->searchMemberCount($conditions);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareClassroomConditions($conditions);
        return $this->getClassroomMemberDao()->searchMembers($conditions, $orderBy, $start, $limit);
    }

    public function getClassroomMember($classroomId, $userId)
    {
        return $this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($classroomId, $userId);
    }

    public function remarkStudent($classroomId, $userId, $remark)
    {
        $member = $this->getClassroomMember($classroomId, $userId);
        if (empty($member)) {
            throw $this->createServiceException('学员不存在，备注失败!');
        }
        $fields = array('remark' => empty($remark) ? '' : (string) $remark);
        return $this->getClassroomMemberDao()->updateMember($member['id'], $fields);
    }

    private function _prepareClassroomConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if(isset($conditions['nickname'])){
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }
        
        return $conditions;
    }

    public function tryManageClassroom($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        $classroom = $this->getClassroom($id);
        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $role=$this->getClassroomRole($id,$user['id']);

        if(!(in_array('admin', $role) or in_array('headerTeacher', $role)) ){
            throw $this->createAccessDeniedException('您不是班主任或管理员，无权操作！');
        }

    }

    public function getClassroomRole($classroomId,$userId)
    {
        $roles=array();

        $member=$this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($classroomId,$userId);

        if($this->getUserService()->hasAdminRoles($userId)){
            
            $roles=array_merge($roles,array('admin'));
        }

        if($this->isHeaderTeacher($classroomId,$userId)){

            $roles=array_merge($roles,array('headerTeacher'));
        }

        if($member && $member['role']=="teacher"){

            $roles=array_merge($roles,array('teacher'));
        }

        if($member && $member['role']=="student"){

            $roles=array_merge($roles,array('student'));
        }

        if($member && $member['role']=="aduitor"){

            $roles=array_merge($roles,array('aduitor'));
        }

        return $roles;
    }

    protected function isHeaderTeacher($classroomId,$userId)
    {
        $classroom=$this->getClassroom($classroomId);

        if($classroom['headerTeacherId'] == $userId )
            return true;

        return false;
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getLogService() 
    {
        return $this->createService('System.LogService');
    }

    protected function getClassroomDao() 
    {
        return $this->createDao('Classroom.ClassroomDao');
    }

    protected function getClassroomMemberDao() 
    {
        return $this->createDao('Classroom.ClassroomMemberDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getClassroomCourseDao() 
    {
        return $this->createDao('Classroom.ClassroomCourseDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}

