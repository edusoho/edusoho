<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MaterialServiceTest extends BaseTestCase
{   
    public function testAddMaterial()
    {

    }

    public function testFindCourseMaterials()
    {
        
    }

    public function testDownloadMaterials()
    {

    }

    public function testDeleteMaterials()
    {

    }

    public function testCourseLatestMaterialsBlock()
    {

    }

    protected function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password']= "user";
        return $this->getUserService()->register($user);
    }

    protected function createCourse(){
        $course = array(
            'type'=>'offline',
            'price' => 1000,
            'title' =>'线下课程',
            'tags'=> array('2'),
            'categoryId'=>8,
            'startTime'=>1367361000,
            'endTime'=>1367772000,
            'locationId'=>120112,
            'address'=>'test');
        return $this->getCourseService()->createCourse($course);
    }


    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}