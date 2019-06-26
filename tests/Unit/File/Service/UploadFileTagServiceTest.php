<?php

namespace Tests\Unit\File\Service;

use Biz\BaseTestCase;
use Biz\File\Dao\UploadFileTagDao;
use Biz\File\Service\UploadFileTagService;

class UploadFileTagServiceTest extends BaseTestCase
{
    public function testAdd()
    {
        $fields = array(
            'fileId' => 2,
            'tagId' => 3,
        );
        $tag = $this->getUploadFileTagService()->add($fields);
        $this->assertEquals(1, $tag['id']);
        $this->assertEquals(2, $tag['fileId']);
        $this->assertEquals(3, $tag['tagId']);
    }

    public function testGet()
    {
        $expected = $this->addUploadFileTag(2, 3);
        $tag = $this->getUploadFileTagService()->get($expected['id']);
        $this->assertArrayValueEquals($expected, $tag);
    }

    public function testFindByFileId()
    {
        $tag1 = $this->addUploadFileTag(2, 3);
        $tag2 = $this->addUploadFileTag(2, 1);
        $tag3 = $this->addUploadFileTag(3, 1);

        $tags = $this->getUploadFileTagService()->findByFileId(1);
        $this->assertCount(0, $tags);

        $tags = $this->getUploadFileTagService()->findByFileId(3);
        $this->assertCount(1, $tags);

        $tags = $this->getUploadFileTagService()->findByFileId(2);
        $this->assertCount(2, $tags);
    }

    public function testByTagId()
    {
        $tag1 = $this->addUploadFileTag(2, 3);
        $tag2 = $this->addUploadFileTag(2, 1);
        $tag3 = $this->addUploadFileTag(3, 1);

        $tags = $this->getUploadFileTagService()->findByTagId(1);
        $this->assertCount(2, $tags);

        $tags = $this->getUploadFileTagService()->findByTagId(3);
        $this->assertCount(1, $tags);

        $tags = $this->getUploadFileTagService()->findByTagId(2);
        $this->assertCount(0, $tags);
    }

    public function testDelete()
    {
        $tag = $this->addUploadFileTag(2, 3);

        $count = $this->getUploadFileTagService()->delete($tag['id']);
        $tagDeleted = $this->getUploadFileTagService()->get($tag['id']);

        $this->assertEquals(1, $count);
        $this->assertEmpty($tagDeleted);
    }

    public function testDeleteByFileId()
    {
        $tag1 = $this->addUploadFileTag(2, 3);
        $tag2 = $this->addUploadFileTag(2, 1);
        $tag3 = $this->addUploadFileTag(3, 1);

        $count = $this->getUploadFileTagService()->deleteByFileId(2);
        $tags = $this->getUploadFileTagService()->findByFileId(2);

        $this->assertEquals(2, $count);
        $this->assertEmpty($tags);

        $count = $this->getUploadFileTagService()->deleteByFileId(3);
        $tags = $this->getUploadFileTagService()->findByFileId(3);

        $this->assertEquals(1, $count);
        $this->assertEmpty($tags);
    }

    public function testDeleteByTagId()
    {
        $tag1 = $this->addUploadFileTag(2, 3);
        $tag2 = $this->addUploadFileTag(2, 1);
        $tag3 = $this->addUploadFileTag(3, 1);

        $count = $this->getUploadFileTagService()->deleteByTagId(1);
        $tags = $this->getUploadFileTagService()->findByTagId(1);

        $this->assertEquals(2, $count);
        $this->assertEmpty($tags);

        $count = $this->getUploadFileTagService()->deleteByTagId(3);
        $tags = $this->getUploadFileTagService()->findByTagId(3);

        $this->assertEquals(1, $count);
        $this->assertEmpty($tags);
    }

    public function testEdit()
    {
        $tag1 = $this->addUploadFileTag(1, 1);
        $tag2 = $this->addUploadFileTag(1, 4);
        $tag3 = $this->addUploadFileTag(2, 2);
        $tag4 = $this->addUploadFileTag(2, 3);

        $fileIds = array(1, 2);
        $result = $this->getUploadFileTagService()->edit($fileIds, array());

        $tags = $this->getUploadFileTagService()->findByFileId(1);

        $this->assertEmpty($result);
        $this->assertEmpty($tags);

        $tag1 = $this->addUploadFileTag(1, 1);
        $tag2 = $this->addUploadFileTag(1, 4);
        $tag3 = $this->addUploadFileTag(2, 2);
        $tag4 = $this->addUploadFileTag(2, 3);

        $fileIds = array(1, 2);
        $tagIds = array(2, 3);

        $result = $this->getUploadFileTagService()->edit($fileIds, $tagIds);
        $tags = $this->getUploadFileTagService()->findByFileId(1);

        $expected = array(
            'id' => 12,
            'fileId' => 2,
            'tagId' => 3,
        );

        $this->assertArraySternEquals($expected, $result);
        $this->assertCount(2, $tags);
    }

    /**
     * @return UploadFileTagService
     */
    private function getUploadFileTagService()
    {
        return $this->createService('File:UploadFileTagService');
    }

    /**
     * @return UploadFileTagDao
     */
    private function getUploadFileTagDao()
    {
        return $this->createDao('File:UploadFileTagDao');
    }

    private function addUploadFileTag($fileId, $tagId)
    {
        $fields = array(
            'fileId' => $fileId,
            'tagId' => $tagId,
        );

        return $this->getUploadFileTagService()->add($fields);
    }
}
