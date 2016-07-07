<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\UserService;
use Topxia\Service\User\UserCommonAdminService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;


class UserCommonAdminServiceTest extends BaseTestCase
{
    public function testAddCommonAdmin()
    {   
        $field=array(
            'url'=>"http://www.esdev.com:81/",
            'title'=>"ceshi",
            'userId'=>1,
        );

        $returnField=$this->getUserCommonAdminService()->addCommonAdmin($field);

        $this->assertEquals(1,$returnField['id']);
        $this->assertEquals('http://www.esdev.com:81/',$returnField['url']);
        $this->assertEquals($field['title'],$returnField['title']);
        $this->assertEquals($field['userId'],$returnField['userId']);

    }

    public function testgetCommonAdmin()
    {   
        $field=array(
            'url'=>"http://www.esdev.com:81/",
            'title'=>"ceshi",
            'userId'=>4,
        );

        $returnField=$this->getUserCommonAdminService()->addCommonAdmin($field);
        
        $fields = $this->getUserCommonAdminService()->getCommonAdmin($returnField['id']);

        $this->assertEquals(true,is_array($fields));

    }   

    public function testFindCommonAdminByUserId()
    {   
        $field=array(
            'url'=>"http://www.esdev.com:81/",
            'title'=>"ceshi",
            'userId'=>2,
        );

        $returnField=$this->getUserCommonAdminService()->addCommonAdmin($field);
        
        $fields = $this->getUserCommonAdminService()->findCommonAdminByUserId($field['userId']);

        $this->assertEquals(true,is_array($fields));

    }    

    public function testGetCommonAdminByUserIdAndUrl()
    {   
        $field=array(
            'url'=>"http://www.esdev.com:81/",
            'title'=>"ceshi",
            'userId'=>3,
        );

        $returnField=$this->getUserCommonAdminService()->addCommonAdmin($field);
        
        $fields = $this->getUserCommonAdminService()->getCommonAdminByUserIdAndUrl($field['userId'],$field['url']);

        $this->assertEquals(true,is_array($fields));

    }    

     /**
     * @group delete
     */
    public function testDeleteCommonAdmin()
    {
        $field=array(
            'url'=>"http://www.esdev.com:81/",
            'title'=>"ceshi",
            'userId'=>1,
        );

        $returnField=$this->getUserCommonAdminService()->addCommonAdmin($field);

        $this->getUserCommonAdminService()->deleteCommonAdmin($returnField['id']);
        $getCommon = $this->getUserCommonAdminService()->findCommonAdminByUserId($field['userId']);

        $this->assertNull($getCommon);
    }

    protected function createUser(){
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password']= "user";
        return $this->getUserService()->register($user);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getUserCommonAdminService()
    {
        return $this->getServiceKernel()->createService('User.UserCommonAdminService');
    }
}