<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use Biz\Course\Service\MaterialService;
use Biz\File\Dao\UploadFileDao;

class MaterialServiceTest extends BaseTestCase
{
    public function testFindMaterialsByCopyIdAndLockedCourseIds()
    {
        $this->mockMaterial(array('copyId' => 2));

        $materials = $this->getMaterialService()->findMaterialsByCopyIdAndLockedCourseIds(2, array(1));
        $this->assertEquals(2, $materials[0]['copyId']);
    }

    public function testFindMaterialsByLessonIdAndSource()
    {
        $this->mockMaterial(array('source' => 'classroom', 'lessonId' => 2));

        $materials = $this->getMaterialService()->findMaterialsByLessonIdAndSource(2, 'classroom');
        $this->assertEquals(2, $materials[0]['lessonId']);
        $this->assertEquals('classroom', $materials[0]['source']);
    }

    public function testDeleteMaterialByMaterialId()
    {
        $material = $this->mockMaterial();

        $this->getMaterialService()->deleteMaterialByMaterialId($material['id']);
        $material = $this->getMaterialService()->getMaterial($material['courseId'], $material['id']);

        $this->assertEmpty($material);
    }

    public function testDeleteMaterialsForCourse()
    {
        $this->mockMaterial(array('fileId' => 1));
        $this->mockMaterial(array('fileId' => 2));

        $this->getMaterialService()->deleteMaterials(1, array(1, 2));

        $conditions = array(
            'fileIds' => array(1, 2),
            'type' => 'course',
            'courseSetId' => 1,
        );
        $count = $this->getMaterialService()->countMaterials($conditions);
        $this->assertEquals(0, $count);
    }

    public function testDeleteMaterialsForOpenCourse()
    {
        $this->mockMaterial(array('fileId' => 1, 'type' => 'openCourse', 'courseSetId' => 0));
        $this->mockMaterial(array('fileId' => 2, 'type' => 'openCourse', 'courseSetId' => 0));

        $this->getMaterialService()->deleteMaterials(1, array(1, 2), 'openCourse');

        $conditions = array(
            'fileIds' => array(1, 2),
            'type' => 'openCourse',
            'courseId' => 1,
            'courseSetId' => 0,
        );
        $count = $this->getMaterialService()->countMaterials($conditions);
        $this->assertEquals(0, $count);
    }

    public function testDeleteMaterialsWithNoneMaterial()
    {
        $result = $this->getMaterialService()->deleteMaterials(1, array(1, 2), 'openCourse');

        $this->assertEmpty($result);
    }

    public function testDeleteMaterialsByFileId()
    {
        $material = $this->mockMaterial();

        $this->getMaterialService()->deleteMaterialsByFileId(2);
        $material = $this->getMaterialService()->getMaterial($material['courseId'], $material['id']);

        $this->assertEmpty($material);
    }

    public function testGetMaterial()
    {
        $material = $this->mockMaterial();

        $material = $this->getMaterialService()->getMaterial($material['courseId'], $material['id']);

        $this->assertEquals('test', $material['title']);
    }

    public function testFindCourseMaterials()
    {
        $this->mockMaterial();

        $materials = $this->getMaterialService()->findCourseMaterials(1, 0, 5);

        $this->assertEquals('test', $materials[0]['title']);
        $this->assertEquals(1, count($materials));
    }

    public function testGetMaterialCountByFileId()
    {
        $this->mockMaterial();
        $this->mockMaterial();

        $count = $this->getMaterialService()->getMaterialCountByFileId(2);

        $this->assertEquals(2, $count);
    }

    public function testFindMaterialsByIds()
    {
        $this->mockMaterial();
        $this->mockMaterial();

        $materials = $this->getMaterialService()->findMaterialsByIds(array(1, 2));

        $this->assertEquals('test', $materials[0]['title']);
        $this->assertEquals(2, count($materials));
    }

    public function testSearchFileIds()
    {
        $result = $this->getMaterialService()->searchFileIds(array('courseId' => 1), array('createdTime' => 'DESC'), 0, 1);
        $this->assertEmpty($result);

        $this->mockMaterial();
        $this->mockMaterial();

        $result = $this->getMaterialService()->searchFileIds(array('courseId' => 1), array('createdTime' => 'DESC'), 0, 1);
        $this->assertEquals(2, $result[0]);
    }

    public function testSearchMaterialCountGroupByFileId()
    {
        $this->mockMaterial();
        $this->mockMaterial(array('fileId' => 1));

        $count = $this->getMaterialService()->searchMaterialCountGroupByFileId(array('fileIds' => array(1, 2)));

        $this->assertEquals(2, $count);
    }

    public function testFindUsedCourseMaterials()
    {
        $this->mockMaterial(array('lessonId' => 1));
        $this->mockMaterial(array('fileId' => 1, 'lessonId' => 2));

        $files = $this->getMaterialService()->findUsedCourseMaterials(array(1, 2), 1);

        $this->assertEquals('coursematerial', $files[1][0]);
        $this->assertEquals('coursematerial', $files[2][0]);
    }

    public function testFindUsedCourseSetMaterials()
    {
        $this->mockMaterial(array('lessonId' => 1));
        $this->mockMaterial(array('fileId' => 1, 'lessonId' => 2));

        $files = $this->getMaterialService()->findUsedCourseSetMaterials(array(1, 2), 1);

        $this->assertEquals('coursematerial', $files[1][0]);
        $this->assertEquals('coursematerial', $files[2][0]);
    }

    public function testFindFullFilesAndSort()
    {
        $result = $this->getMaterialService()->findFullFilesAndSort(array());
        $this->assertEmpty($result);

        $this->mockMaterial(array('fileId' => 3));
        $this->mockMaterial(array('fileId' => 4));
        $this->mockUploadFile();

        $result = $this->getMaterialService()->findFullFilesAndSort(array(0 => array('fileId' => 3), 1 => array('fileId' => 4)));
        $this->assertEquals(3, $result[0]['fileId']);
        $this->assertEquals(4, $result[1]['fileId']);
    }

    public function testBatchCreateMaterials()
    {
        $result = $this->getMaterialService()->batchCreateMaterials(array());
        $this->assertEmpty($result);

        $materials = $this->createMaterials();
        $result = $this->getMaterialService()->batchCreateMaterials($materials);
        $this->assertTrue($result);
    }

    protected function mockMaterial($fields = array())
    {
        $material = array(
            'courseId' => 1,
            'courseSetId' => 1,
            'title' => 'test',
            'fileId' => 2,
            'userId' => 1,
        );

        $material = array_merge($material, $fields);

        return $this->getMaterialService()->addMaterial($material, array());
    }

    protected function createMaterials()
    {
        $materials = array();

        $material = array(
            'courseId' => 1,
            'courseSetId' => 1,
            'title' => 'test_1',
            'fileId' => 2,
            'userId' => 1,
        );
        $materials[] = $material;

        $material = array(
            'courseId' => 2,
            'courseSetId' => 2,
            'title' => 'test_2',
            'fileId' => 2,
            'userId' => 1,
        );
        $materials[] = $material;

        return $materials;
    }

    protected function mockUploadFile()
    {
        $file1 = array(
            'id' => '3',
            'globalId' => md5('test'),
            'hashId' => 'course-task-14/20171212114426-njm92j6bgw0wgooo',
            'targetId' => '14',
            'targetType' => 'course-task',
            'filename' => 'test',
            'ext' => 'mp4',
            'etag' => 'course-task-14/20171212114426-njm92j6bgw0wgooo',
            'convertHash' => 'course-task-14/20171212114426-njm92j6bgw0wgooo',
            'type' => 'video',
            'storage' => 'cloud',
            'createdUserId' => '1',
            'createdTime' => time(),
        );
        $file2 = array(
            'id' => '4',
            'globalId' => md5('test'),
            'hashId' => 'course-task-15/20171212114426-njm92j6bgw0wgooo',
            'targetId' => '15',
            'targetType' => 'course-task',
            'filename' => 'test',
            'ext' => 'mp4',
            'etag' => 'course-task-15/20171212114426-njm92j6bgw0wgooo',
            'convertHash' => 'course-task-15/20171212114426-njm92j6bgw0wgooo',
            'type' => 'video',
            'storage' => 'cloud',
            'createdUserId' => '1',
            'createdTime' => time(),
        );
        $this->getUploadFileDao()->create($file1);
        $this->getUploadFileDao()->create($file2);
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }
}
