<?php

namespace Topxia\Service\Thread\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;

class ThreadServiceTest extends BaseTestCase
{
    public function testCreateThread()
    {   
        $user = $this->createUser();
        $textThread = array(
            'title' => 'textgroup',
            'content'=>"aaaaaa",
            'targetId'=>'1',
            'type'=>'discussion',
            'targetType'=>'classroom'
        );

        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = $this->getThreadService()->createThread($textThread);

        $this->assertEquals(1,$thread['id']);

        $this->assertEquals($textThread['title'],$thread['title']);

        $this->assertEquals($textThread['content'],$thread['content']);

        $this->assertEquals('open',$thread['status']);

    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    private function createUser(){

        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password']= "user";
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        
        return $this->getUserService()->register($user);
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}