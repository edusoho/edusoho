<?php

namespace Tests\Unit\CloudFile;

use Biz\BaseTestCase;
use Mockery;
use QiQiuYun\SDK\Service\ResourceService;

class CloudFileServiceTest extends BaseTestCase
{
    public function testSearchEmptyresType()
    {
        $this->_mockCloudFileImplementor();
        $this->_mockUploadFileTagService();
        $this->_mockUploadFileService();
        $this->_mockUserService();

        //条件含tags
        $conditions = array('tags' => 1);
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        //条件含useStatus
        $conditions = array('useStatus' => 'used');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        $conditions = array('useStatus' => 'unUsed', 'no' => '56b1b123fe5847719b7234d96ba8af69');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        //条件含keywords
        $this->_mockCourseSetService();
        $this->_mockMaterialService();
        $conditions = array('keywords' => 'course name', 'searchType' => 'course');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        $conditions = array('keywords' => 'file name', 'searchType' => 'title');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        $conditions = array('keywords' => 'nickname', 'searchType' => 'user');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        $conditions = array('keywords' => 'nickname', 'searchType' => 'user1');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));

        $conditions = array('keywords' => 'nickname', 'searchType' => 'user', 'ids' => array(-1));
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));
    }

    public function testSearchByResType()
    {
        $this->_mockUploadFileService();
        $this->_mockUserService();

        $conditions = array('resType' => 'normal');
        $result = $this->getCloudFileService()->search($conditions, 0, 5);
        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(2, count($result['createdUsers']));
    }

    public function testEditEmptyGlobalId()
    {
        $result = $this->getCloudFileService()->edit('', array());
        $this->assertFalse($result);
    }

    public function testEditEmptyFile()
    {
        $this->_mockCloudFileImplementor();

        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array(),
                ),
            )
        );

        $fields = array('name' => 'updateName', 'tags' => 'tag1');
        $result = $this->getCloudFileService()->edit(1, $fields);
        $this->assertEquals('updateName', $result['name']);
        $this->assertEquals('tag1', $result['tags']);
    }

    public function testEditFileExist()
    {
        $this->_mockCloudFileImplementor();

        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array('id' => 1, 'no' => '56b1b123fe5847719b7234d96ba8af69', 'createdUserId' => 1),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array(),
                ),
            )
        );

        $fields = array('name' => 'updateName', 'tags' => 'tag1');
        $result = $this->getCloudFileService()->edit(1, $fields);
        $this->assertTrue($result['success']);
    }

    public function testDeleteEmptyGlobalId()
    {
        $result = $this->getCloudFileService()->delete('');
        $this->assertFalse($result);
    }

    public function testDeleteEmptyFile()
    {
        $this->_mockCloudFileImplementor();
        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array(),
                ),
            )
        );

        $result = $this->getCloudFileService()->delete('56b1b123fe5847719b7234d96ba8af69');
        $this->assertTrue($result);
    }

    public function testDeleteFileExist()
    {
        $this->_mockCloudFileImplementor();
        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array('id' => 1, 'no' => '56b1b123fe5847719b7234d96ba8af69', 'createdUserId' => 1),
                ),
                array(
                    'functionName' => 'deleteFile',
                    'returnValue' => true,
                ),
            )
        );

        $result = $this->getCloudFileService()->delete('56b1b123fe5847719b7234d96ba8af69');
        $this->assertTrue($result['success']);
    }

    public function testBatchDelete()
    {
        $this->_mockCloudFileImplementor();
        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array('id' => 1, 'no' => '56b1b123fe5847719b7234d96ba8af69', 'createdUserId' => 1),
                ),
                array(
                    'functionName' => 'deleteFile',
                    'returnValue' => true,
                ),
            )
        );

        $result = $this->getCloudFileService()->batchDelete(array('56b1b123fe5847719b7234d96ba8af69'));
        $this->assertTrue($result);

        $result = $this->getCloudFileService()->batchDelete(array());
        $this->assertFalse($result);
    }

    public function testGetByGlobalId()
    {
        $this->_mockCloudFileImplementor();
        $result = $this->getCloudFileService()->getByGlobalId('fbb5c6ef413f4cbbb425d70793a23703');

        $this->assertEquals('fbb5c6ef413f4cbbb425d70793a23703', $result['no']);
    }

    /*public function testPlayer()
    {
        $this->_mockCloudFileImplementor();
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('cloud_access_key' => '123456','cloud_secret_key'=>'123456'),
                ),
            )
        );

        $mockObject = Mockery::mock('ResourceService');
        $mockObject->shouldReceive('generatePlayToken')->times(1)->andReturn('456789');

        $result = $this->getCloudFileService()->player('fbb5c6ef413f4cbbb425d70793a23703', $ssl = false);

        $this->assertEquals('video', $result['player']);
        $this->assertEquals('456789', $result['token']);
    }*/

    public function testDownload()
    {
        $this->_mockCloudFileImplementor();
        $result = $this->getCloudFileService()->download('fbb5c6ef413f4cbbb425d70793a23703');

        $this->assertArrayHasKey('url', $result);
    }

    public function testReconvertFileExist()
    {
        $this->_mockCloudFileImplementor();
        $this->_mockUploadFileService();
        $result = $this->getCloudFileService()->reconvert('fbb5c6ef413f4cbbb425d70793a23703', $options = array());

        $this->assertArrayHasKey('no', $result);
    }

    public function testReconvertFileEmpty()
    {
        $this->_mockCloudFileImplementor();

        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array(),
                ),
            )
        );

        $result = $this->getCloudFileService()->reconvert('fbb5c6ef413f4cbbb425d70793a23703', $options = array());

        $this->assertArrayHasKey('no', $result);
    }

    public function testGetDefaultHumbnails()
    {
        $this->_mockCloudFileImplementor();
        $result = $this->getCloudFileService()->getDefaultHumbnails('fbb5c6ef413f4cbbb425d70793a23703');

        $this->assertArrayHasKey('no', $result);
        $this->assertArrayHasKey('url', $result);
    }

    public function testGetThumbnail()
    {
        $this->_mockCloudFileImplementor();
        $result = $this->getCloudFileService()->getThumbnail('fbb5c6ef413f4cbbb425d70793a23703', array());

        $this->assertArrayHasKey('no', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('status', $result);
    }

    public function testGetStatistics()
    {
        $this->_mockCloudFileImplementor();
        $result = $this->getCloudFileService()->getStatistics($options = array());

        $this->assertArrayHasKey('storage', $result);
        $this->assertArrayHasKey('video', $result);
    }

    public function testDeleteCloudMP4Files()
    {
        $this->_mockCloudFileImplementor();

        $this->mockBiz(
            'User:TokenService',
            array(
                array(
                    'functionName' => 'makeToken',
                    'returnValue' => array('id' => 1, 'token' => '123456'),
                ),
            )
        );
        $result = $this->getCloudFileService()->deleteCloudMP4Files(1, 'callback');

        $this->assertTrue($result['success']);
    }

    public function testHasMp4Video()
    {
        $this->mockBiz(
            'File:CloudFileImplementor',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array('data' => array(array('id' => 1, 'no' => 'fbb5c6ef413f4cbbb425d70793a23703'), array('id' => 2, 'no' => '56b1b123fe5847719b7234d96ba8af69'))),
                ),
            )
        );
        $result = $this->getCloudFileService()->hasMp4Video();

        $this->assertTrue($result);
    }

    public function testNoMp4Video()
    {
        $this->mockBiz(
            'File:CloudFileImplementor',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(),
                ),
            )
        );
        $result = $this->getCloudFileService()->hasMp4Video();

        $this->assertFalse($result);
    }

    private function _mockCloudFileImplementor()
    {
        $this->mockBiz(
            'File:CloudFileImplementor',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array('data' => array(array('id' => 1, 'no' => 'fbb5c6ef413f4cbbb425d70793a23703'), array('id' => 2, 'no' => '56b1b123fe5847719b7234d96ba8af69'))),
                ),
                array(
                    'functionName' => 'updateFile',
                    'returnValue' => array('id' => 1, 'name' => 'updateName', 'no' => '56b1b123fe5847719b7234d96ba8af69', 'tags' => 'tag1'),
                ),
                array(
                    'functionName' => 'deleteFile',
                    'returnValue' => true,
                ),
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array('id' => 1, 'no' => 'fbb5c6ef413f4cbbb425d70793a23703'),
                ),
                array(
                    'functionName' => 'player',
                    'returnValue' => array('player' => 'video'),
                ),
                array(
                    'functionName' => 'download',
                    'returnValue' => array('url' => 'http://demo.edusoho.com'),
                ),
                array(
                    'functionName' => 'reconvert',
                    'returnValue' => '',
                ),
                array(
                    'functionName' => 'getFile',
                    'returnValue' => array('id' => 1, 'no' => 'fbb5c6ef413f4cbbb425d70793a23703'),
                ),
                array(
                    'functionName' => 'getDefaultHumbnails',
                    'returnValue' => array('no' => '123', 'url' => 'http://demo.edusoho.com'),
                ),
                array(
                    'functionName' => 'getThumbnail',
                    'returnValue' => array('no' => '123', 'url' => 'http://demo.edusoho.com', 'status' => 'success'),
                ),
                array(
                    'functionName' => 'getStatistics',
                    'returnValue' => array('storage' => array(), 'video' => array()),
                ),
                array(
                    'functionName' => 'deleteMP4Files',
                    'returnValue' => array('success' => true),
                ),
            )
        );
    }

    private function _mockUploadFileService()
    {
        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'countCloudFilesFromLocal',
                    'returnValue' => 2,
                ),
                array(
                    'functionName' => 'searchCloudFilesFromLocal',
                    'returnValue' => array(array('id' => 1, 'globalId' => 'fbb5c6ef413f4cbbb425d70793a23703', 'createdUserId' => 1, 'processStatus' => 'none'), array('id' => 2, 'globalId' => '56b1b123fe5847719b7234d96ba8af69', 'createdUserId' => 2, 'processStatus' => 'success')),
                ),
                array(
                    'functionName' => 'getFileByGlobalId',
                    'returnValue' => array('id' => 1, 'no' => '56b1b123fe5847719b7234d96ba8af69', 'createdUserId' => 1),
                ),
                array(
                    'functionName' => 'findFilesByIds',
                    'returnValue' => array(array('id' => 1, 'globalId' => '56b1b123fe5847719b7234d96ba8af69'), array('id' => 2, 'globalId' => '56b1b123fe5847719b7234d96ba8af69')),
                ),
            )
        );
    }

    private function _mockUserService()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(array('id' => 1, 'nickname' => 'user1'), array('id' => 2, 'nickname' => 'user2')),
                ),
                array(
                    'functionName' => 'searchUsers',
                    'returnValue' => array(array('id' => 1, 'nickname' => 'user1'), array('id' => 2, 'nickname' => 'user2')),
                ),
            )
        );
    }

    private function _mockUploadFileTagService()
    {
        $this->mockBiz(
            'File:UploadFileTagService',
            array(
                array(
                    'functionName' => 'findByTagId',
                    'returnValue' => array(array('id' => 1, 'fileId' => 1, 'tagId' => 1)),
                    'withParams' => array(1),
                ),
            )
        );
    }

    private function _mockCourseSetService()
    {
        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'findCourseSetsLikeTitle',
                    'returnValue' => array(array('id' => 1)),
                ),
            )
        );
    }

    private function _mockMaterialService()
    {
        $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'searchMaterials',
                    'returnValue' => array(array('id' => 1)),
                ),
            )
        );
    }

    /**
     * @return LogService
     */
    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }
}
