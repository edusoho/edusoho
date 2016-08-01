<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class MaterialServiceTest extends BaseTestCase
{
    public function testUploadMaterial()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $name   = 'File.UploadFileService';
        $params = array(
            array(
                'functionName' => 'getFile',
                'runTimes'     => 1,
                'withParams'   => array(1),
                'returnValue'  => array(
                    'id'            => 1,
                    'storage'       => 'cloud',
                    'filename'      => 'test',
                    'createdUserId' => 1,
                    'fileSize'      => 1024
                )
            ),
            array(
                'functionName' => 'waveUploadFile',
                'runTimes'     => 1,
                'withParams'   => array(1),
                'returnValue'  => array(
                    'id'        => 1,
                    'usedCount' => 1
                )
            )
        );
        $this->mock($name, $params);

        $fields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'source'   => 'coursematerial',
            'type'     => 'course',
            'fileId'   => 1
        );
        $material = $this->getMaterialService()->uploadMaterial($fields);

        $this->assertEquals(1, $material['fileId']);
        $this->assertEquals('coursematerial', $material['source']);
        $this->assertEquals('course', $material['type']);
    }

    public function testAddMaterial()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material = $this->getMaterialService()->addMaterial($fields, $fields);

        $this->assertEquals($fields['fileId'], $material['fileId']);
        $this->assertEquals($fields['source'], $material['source']);

        $lesson = $this->getCourseService()->getLesson($lesson['id']);
        $this->assertEquals(1, $lesson['materialNum']);
    }

    public function testUpdateMaterial()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields = array(
            'courseId'    => $course['id'],
            'lessonId'    => 0,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material = $this->getMaterialService()->addMaterial($fields, $fields);

        $updateFields   = array('lessonId' => $lesson['id']);
        $updateMaterial = $this->getMaterialService()->updateMaterial($material['id'], $updateFields, array('fileId' => $fields['fileId']));

        $this->assertEquals($lesson['id'], $updateMaterial['lessonId']);
        $this->assertEquals($fields['source'], $updateMaterial['source']);
    }

    public function testDeleteMaterial()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields = array(
            'courseId'    => $course['id'],
            'lessonId'    => 0,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material = $this->getMaterialService()->addMaterial($fields, $fields);
        $this->assertEquals($fields['fileId'], $material['fileId']);
        $this->assertEquals($fields['source'], $material['source']);

        $this->getMaterialService()->deleteMaterial($course['id'], $material['id']);
        $material = $this->getMaterialService()->getMaterial($course['id'], $material['id']);
        $this->assertNull($material);
    }

    public function testFindMaterialsByCopyIdAndLockedCourseIds()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields = array(
            'courseId'    => $course['id'],
            'lessonId'    => 0,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material = $this->getMaterialService()->addMaterial($fields, $fields);

        $fields = array(
            'courseId'    => 2,
            'lessonId'    => 0,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'copyId'      => $material['id'],
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields, $fields);

        $fields = array(
            'courseId'    => 3,
            'lessonId'    => 0,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'copyId'      => $material['id'],
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields, $fields);

        $materials = $this->getMaterialService()->findMaterialsByCopyIdAndLockedCourseIds($material['id'], array(2, 3));

        $this->assertEquals(2, count($materials));
    }

    public function testDeleteMaterialsByLessonId()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields1 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);
        $this->assertEquals($fields1['fileId'], $material1['fileId']);
        $this->assertEquals($fields1['source'], $material1['source']);

        $fields2 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'opencourselesson',
            'type'        => 'openCourse',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);
        $this->assertEquals($fields2['fileId'], $material2['fileId']);
        $this->assertEquals($fields2['source'], $material2['source']);

        $this->getMaterialService()->deleteMaterialsByLessonId($lesson['id'], $courseType = 'course');
        $material = $this->getMaterialService()->getMaterial($course['id'], $material1['id']);

        $this->assertNull($material);
    }

    public function testDeleteMaterialsByCourseId()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields1 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);
        $this->assertEquals($fields1['fileId'], $material1['fileId']);
        $this->assertEquals($fields1['source'], $material1['source']);

        $fields2 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'opencourselesson',
            'type'        => 'openCourse',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);
        $this->assertEquals($fields2['fileId'], $material2['fileId']);
        $this->assertEquals($fields2['source'], $material2['source']);

        $this->getMaterialService()->deleteMaterialsByCourseId($course['id'], $courseType = 'course');
        $material = $this->getMaterialService()->getMaterial($course['id'], $material1['id']);

        $this->assertNull($material);
    }

    public function testDeleteMaterials()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields1 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);
        $this->assertEquals($fields1['fileId'], $material1['fileId']);
        $this->assertEquals($fields1['source'], $material1['source']);

        $fields2 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 2,
            'link'        => '',
            'title'       => 'test1.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);
        $this->assertEquals($fields2['fileId'], $material2['fileId']);
        $this->assertEquals($fields2['source'], $material2['source']);

        $this->getMaterialService()->deleteMaterials($course['id'], array($material1['fileId'], $material2['fileId']), $courseType = 'course');
        $material1 = $this->getMaterialService()->getMaterial($course['id'], $material1['id']);
        $material2 = $this->getMaterialService()->getMaterial($course['id'], $material2['id']);

        $this->assertNull($material1);
        $this->assertNull($material2);
    }

    public function testDeleteMaterialsByFileId()
    {
        $fields1 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);
        $this->assertEquals($fields1['fileId'], $material1['fileId']);
        $this->assertEquals($fields1['source'], $material1['source']);

        $fields2 = array(
            'courseId'    => 2,
            'lessonId'    => 4,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);
        $this->assertEquals($fields2['fileId'], $material2['fileId']);
        $this->assertEquals($fields2['source'], $material2['source']);

        $this->getMaterialService()->deleteMaterialsByFileId(1);
        $material1 = $this->getMaterialService()->getMaterial(1, $material1['id']);
        $material2 = $this->getMaterialService()->getMaterial(2, $material2['id']);

        $this->assertNull($material1);
        $this->assertNull($material2);

    }

    public function testGetMaterial()
    {
        $fields1 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);

        $material = $this->getMaterialService()->getMaterial(1, $material1['id']);
        $this->assertEquals($fields1['fileId'], $material['fileId']);
        $this->assertEquals($fields1['source'], $material['source']);
    }

    public function testSearchMaterials()
    {
        $fields1 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);

        $fields2 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'opencourselesson',
            'type'        => 'openCourse',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);

        $conditions = array('type' => 'course');
        $materials  = $this->getMaterialService()->searchMaterials($conditions, array('createdTime', 'DESC'), 0, 1);

        $this->assertEquals($material1['id'], $materials[0]['id']);
        $this->assertEquals($material1['source'], $materials[0]['source']);
    }

    public function testSearchMaterialCount()
    {
        $fields1 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);

        $fields2 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'opencourselesson',
            'type'        => 'openCourse',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);

        $conditions     = array('type' => 'course');
        $materialsCount = $this->getMaterialService()->searchMaterialCount($conditions);

        $this->assertEquals(1, $materialsCount);
    }

    public function testSearchMaterialsGroupByFileId()
    {
        $fields1 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);

        $fields2 = array(
            'courseId'    => 1,
            'lessonId'    => 1,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);

        $fields3 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'opencourselesson',
            'type'        => 'openCourse',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material3 = $this->getMaterialService()->addMaterial($fields3, $fields3);

        $conditions = array('courseId' => 1, 'type' => 'course');
        $materials  = $this->getMaterialService()->searchMaterialsGroupByFileId($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);

        $this->assertEquals(1, count($materials));
    }

    public function testSearchMaterialCountGroupByFileId()
    {
        $fields1 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);

        $fields2 = array(
            'courseId'    => 1,
            'lessonId'    => 1,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);

        $fields3 = array(
            'courseId'    => 1,
            'lessonId'    => 2,
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'opencourselesson',
            'type'        => 'openCourse',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material3 = $this->getMaterialService()->addMaterial($fields3, $fields3);

        $conditions    = array('courseId' => 1, 'type' => 'course');
        $materialCount = $this->getMaterialService()->searchMaterialCountGroupByFileId($conditions);

        $this->assertEquals(1, $materialCount);
    }

    public function testFindUsedCourseMaterials()
    {
        $course = $this->createCourse();
        $lesson = $this->createdCourseLesson($course['id']);

        $fields1 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'coursematerial',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material1 = $this->getMaterialService()->addMaterial($fields1, $fields1);

        $fields2 = array(
            'courseId'    => $course['id'],
            'lessonId'    => $lesson['id'],
            'description' => '',
            'userId'      => $this->getCurrentUser()->id,
            'source'      => 'courselesson',
            'type'        => 'course',
            'fileId'      => 1,
            'link'        => '',
            'title'       => 'test.doc',
            'fileSize'    => 1024,
            'createdTime' => time()
        );
        $material2 = $this->getMaterialService()->addMaterial($fields2, $fields2);

        $usedMaterials = $this->getMaterialService()->findUsedCourseMaterials(array(1), $course['id']);

        $this->assertArrayHasKey(1, $usedMaterials);
        $usedKeys = $usedMaterials[1];
        $this->assertContains('coursematerial', $usedKeys);
        $this->assertContains('courselesson', $usedKeys);
    }

    protected function createUser()
    {
        $user             = array();
        $user['email']    = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        return $this->getUserService()->register($user);
    }

    protected function createCourse()
    {
        $course = array(
            'title' => 'material-test',
            'type'  => 'normal'
        );
        return $this->getCourseService()->createCourse($course);
    }

    protected function createdCourseLesson($courseId)
    {
        $fields = array(
            'title'    => 'material-lesson-test',
            'courseId' => $courseId,
            'type'     => 'text'
        );

        return $this->getCourseService()->createLesson($fields);
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
