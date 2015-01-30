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
        return $this->getClassroomDao()->getClassroom($id);
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


    public function canTakeClassroom($classroom)
    {
        $classroom = !is_array($classroom) ? $this->getClassroom(intval($classroom)) : $classroom;
        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        $member = $this->getMemberDao()->getMemberByClassIdAndUserId($classroom['id'], $user['id']);
        if ($member and in_array($member['role'], array('teacher', 'student','aduitor'))) {
            return true;
        }

        return false;
    }

    public function tryTakeClassroom($classId)
    {
        $classroom = $this->getClassroom($classId);
        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }
        if ($classroom['status'] != 'published') {
            throw $this->createAccessDeniedException('班级未发布,无法查看,请联系管理员！');
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('您尚未登录用户，请登录后再查看！');
        }

        $member = $this->getMemberDao()->getMemberByClassIdAndUserId($classId, $user['id']);
        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return array($classroom, $member);
        }

        if (empty($member) or !in_array($member['role'], array('teacher', 'student','aduitor'))) {
            throw $this->createAccessDeniedException('您不是班级学员，不能查看课程内容，请先购买班级！');
        }

        return array($classroom, $member);
    }


    public function canManageClassroom($targetId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        $classroom = $this->getClassroom($targetId);
        if (empty($classroom)) {
            return $user->isAdmin();
        }

        $member = $this->getMemberDao()->getMemberByClassIdAndUserId($targetId, $user->id);
        if ($member and ($member['role'] == 'teacher')) {
            return true;
        }

        return false;
    }

    public function getClassroomMember($classId, $userId)
    {
        return $this->getMemberDao()->getMemberByClassIdAndUserId($classId, $userId);
    }

    public function updateClassroom($id,$fields)
    {   
        $classroom=$this->getClassroomDao()->updateClassroom($id,$fields);

        return $classroom;
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

    private function getMemberDao ()
    {
        return $this->createDao('Classroom.ClassroomMemberDao');
    }

    protected function getClassroomCourseDao() 
    {
        return $this->createDao('Classroom.ClassroomCourseDao');
    }

}
