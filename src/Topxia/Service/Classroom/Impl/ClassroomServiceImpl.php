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

    public function isClassroomStudent($classroomId, $studentId)
    {
        $classroomMember = $this->getClassroomMemberDao()->findClassroomMemberByClassIdAndUserIdAndRole($classroomId,$studentId,"student");
        if(!empty($classroomMember)){
            return true;
        }
        return false;
    }

    public function becomeStudent($classroomId, $userId, $info = array())
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        if($classroom['status'] != 'published') {
            throw $this->createServiceException('不能加入未发布班级');
        }

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入班级失败！");
        }

        $member = $this->getClassroomMemberDao()->findClassroomMemberByClassIdAndUserIdAndRole($courseId, $userId, "student");
        if ($member) {
            throw $this->createServiceException("用户(#{$userId})已加入该班级！");
        }

        $levelChecked = '';
        if (!empty($info['becomeUseMember'])) {
            $levelChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId']);
            if ($levelChecked != 'ok') {
                throw $this->createServiceException("用户(#{$userId})不能以会员身份加入班级！");
            }
            $userMember = $this->getVipService()->getMemberByUserId($user['id']);
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);
            if (empty($order)) {
                throw $this->createServiceException("订单(#{$info['orderId']})不存在，加入班级失败！");
            }
        } else {
            $order = null;
        }

        $fields = array(
            'classId' => $classroomId,
            'userId' => $userId,
            'orderId' => empty($order) ? 0 : $order['id'],
            'levelId' => empty($info['becomeUseMember']) ? 0 : $userMember['levelId'],
            'role' => 'student',
            'remark' => empty($order['note']) ? '' : $order['note'],
            'createdTime' => time()
        );

        if (empty($fields['remark'])) {
            $fields['remark'] = empty($info['note']) ? '' : $info['note'];
        }

        $member = $this->getClassroomMemberDao()->addMember($fields);
                
        $setting = $this->getSettingService()->get('course', array());
        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);
            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
        }

        $fields = array(
            'studentNum'=> $this->getClassroomStudentCount($classroomId),
        );
        if ($order) {
            $fields['income'] = $this->getOrderService()->sumOrderPriceByTarget('classroomId', $classroomId);
        }
        $this->getClassroomDao()->updateClassroom($classroomId, $fields);
        if($classroom['status'] == 'published' ){
            $this->getStatusService()->publishStatus(array(
                'type' => 'become_student',
                'objectType' => 'classroom',
                'objectId' => $classroomId,
                'properties' => array(
                    'classroom' => $this->simplifyClassroom($classroom),
                )
            ));
        }
        return $member;
    }

    public function getClassroomStudentCount($classroomId)
    {
        return $this->getClassroomMemberDao()->getClassroomStudentCount($classroomId);
    }

    private function simplifyClassroom($classroom)
    {
        return array(
            'id' => $classroom['id'],
            'title' => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about' => StringToolkit::plain($classroom['about'], 100),
            'price' => $classroom['price'],
        );
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

    protected function getClassroomCourseDao() 
    {
        return $this->createDao('Classroom.ClassroomCourseDao');
    }
}
