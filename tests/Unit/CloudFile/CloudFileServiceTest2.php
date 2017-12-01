<?php

namespace Tests\Unit\CloudFile;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;
use Biz\BaseTestCase;

class CloudFileServiceTest2 extends BaseTestCase
{
    public function testSearch()
    {
        $conditions = array(
            'resType' => 'video'
        );

        $mockRemoteResources = array(
            array('globalId' => 1, 'createdUserId' => 1, 'processStatus' => 'ok'),
            array('globalId' => 2, 'createdUserId' => 2, 'processStatus' => 'ok'),
            array('globalId' => 3, 'createdUserId' => 3, 'processStatus' => 'ok'),
        );

        $expectedConditions = array('resType' => 'video', 'targetType' => 'video');
        $this->mockBiz('File:UploadFileService', array(
           array('functionName' => 'searchFiles', 'withParams' => array($expectedConditions, array('id' => 'DESC'), 1, 2), 'returnValue' => $mockRemoteResources),
           array('functionName' => 'searchFileCount', 'withParams' => array($expectedConditions), 'returnValue' => 3),
        ));

        $this->mockBiz('User:UserService', array(
            array('functionName' => 'findUsersByIds', 'withParams' => array(array(1, 2, 3)), 'returnValue' => array()),
        ));

        $result = $this->getCloudFileService()->search($conditions,1, 2);

        $this->assertArraySubset($mockRemoteResources, $result['data']);
    }

    public function testEditWithEmptyGlobalId()
    {
        $result = $this->getCloudFileService()->edit(0, array());

        $this->assertFalse($result);
    }

    public function testEditWithEmptyFile()
    {
        $globalId = '1234566799';
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFileByGlobalId', 'withParams' => array($globalId), 'returnValue' => null)
        ));

        $fields = array(
            'name' => 'learning',
            'tags' => 'tag1,tag2',
            'description' => 'just a desc'
        );

        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'updateFile', 'withParams' => array($globalId, $fields), 'returnValue' => true)
        ));

        $result = $this->getCloudFileService()->edit($globalId, $fields);

        $this->assertTrue($result);
    }

    public function testEditWithExistFile()
    {
        $globalId = '1234566799';
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFileByGlobalId', 'withParams' => array($globalId), 'returnValue' => array('id' => 1)),
            array('functionName' => 'update', 'returnValue' => array('id' => 1)),
        ));

        $result = $this->getCloudFileService()->edit($globalId, array());

        $this->assertEquals(array('success' => true), $result);
    }

    public function testDelete()
    {
        $result = $this->getCloudFileService()->delete(0);
        $this->assertFalse($result);

        $globalId = '1234566799';
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFileByGlobalId', 'withParams' => array($globalId), 'returnValue' => array('id' => 1)),
            array('functionName' => 'deleteFile', 'returnValue' => array('id' => 1)),
        ));
        $result = $this->getCloudFileService()->delete($globalId);
        $this->assertEquals(array('success' => true), $result);


        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFileByGlobalId', 'withParams' => array($globalId), 'returnValue' => null)
        ));
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'deleteFile', 'withParams' => array(array('globalId' => $globalId)), 'returnValue' => true)
        ));
        $result = $this->getCloudFileService()->delete($globalId);
        $this->assertTrue($result);
    }

    public function testBatchDelete()
    {
        $result = $this->getCloudFileService()->batchDelete(0);
        $this->assertFalse($result);

        $result = $this->getCloudFileService()->batchDelete(array(false, false));
        $this->assertTrue($result);
    }

    public function testGetByGlobalId()
    {
        $globalId = '1234566799';
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'getFileByGlobalId', 'withParams' => array($globalId), 'returnValue' => true)
        ));
        $result = $this->getCloudFileService()->getByGlobalId($globalId);
        $this->assertTrue($result);
    }

    public function testPlayer()
    {
        $globalId = '1234566799';
        $ssl = false;
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'player', 'withParams' => array($globalId, $ssl), 'returnValue' => null)
        ));
        $result = $this->getCloudFileService()->player($globalId, $ssl);
        $this->assertEmpty($result);


        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'player', 'withParams' => array($globalId, $ssl), 'returnValue' => array('id' => 123))
        ));
        $this->mockBiz('System:SettingService', array(
           array('functionName' => 'get', 'returnValue' => array('cloud_access_key' => 1, 'cloud_secret_key' => 2))
        ));
        $result = $this->getCloudFileService()->player($globalId, $ssl);
        $this->assertArrayHasKey('token', $result);
    }

    public function testDownload()
    {
        $globalId = '1234566799';
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'download', 'withParams' => array($globalId), 'returnValue' => true)
        ));
        $result = $this->getCloudFileService()->download($globalId);
        $this->assertTrue($result);
    }

    public function testReconvert()
    {
        $globalId = '1234566799';
        $options = array();
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'reconvert', 'withParams' => array($globalId, $options), 'returnValue' => true),
            array('functionName' => 'getFile', 'withParams' => array(array('globalId' => $globalId)), 'returnValue' => true),
        ));

        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFileByGlobalId', 'withParams' => array($globalId), 'returnValue' => null),
        ));
        $result = $this->getCloudFileService()->reconvert($globalId, $options);
        $this->assertTrue($result);
    }

    public function testGetDefaultHumbnails()
    {
        $globalId = '1234566799';
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'getDefaultHumbnails', 'withParams' => array($globalId), 'returnValue' => true)
        ));
        $result = $this->getCloudFileService()->getDefaultHumbnails($globalId);
        $this->assertTrue($result);
    }

    public function testGetThumbnail()
    {
        $globalId = '1234566799';
        $options = array();
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'getThumbnail', 'withParams' => array($globalId, $options), 'returnValue' => true)
        ));
        $result = $this->getCloudFileService()->getThumbnail($globalId, $options);
        $this->assertTrue($result);
    }

    public function testGetStatistics()
    {
        $options = array();
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'getStatistics', 'withParams' => array($options), 'returnValue' => true)
        ));
        $result = $this->getCloudFileService()->getStatistics($options);
        $this->assertTrue($result);
    }

    public function testDeleteCloudMP4Files()
    {
        $userId = 1;
        $callback = '//callback';
        $this->mockBiz('User:TokenService', array(
            array(
                'functionName' => 'makeToken',
                'withParams' => array('mp4_delete.callback', array('userId' => $userId, 'duration' => TimeMachine::ONE_MONTH, 'times' => 1)),
                'returnValue' => array('token' => '1234'))
        ));

        $callbackParams = $callback.'&token=1234';
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'deleteMP4Files', 'withParams' => array($callbackParams), 'returnValue' => true)
        ));

        $result = $this->getCloudFileService()->deleteCloudMP4Files($userId, $callback);

        $this->assertTrue($result);
    }

    public function testHasMp4Video()
    {
        $params = array(
            'mcStatus' => 'yes',
            'page' => 1,
            'start' => 0,
            'limit' => 1,
        );
        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'search', 'withParams' => array($params), 'returnValue' => array('data' => 124))
        ));
        $result = $this->getCloudFileService()->hasMp4Video();
        $this->assertTrue($result);

        $this->mockBiz('File:CloudFileImplementor', array(
            array('functionName' => 'search', 'withParams' => array($params), 'returnValue' => array())
        ));
        $result = $this->getCloudFileService()->hasMp4Video();
        $this->assertFalse($result);
    }

    public function testFilterConditions()
    {
        $conditions = array(
            'name' => 12,
            'id' => 1,
            'emptyStr' => '',
            'false' => false,
            'zero' => 0,
        );

        $cloudFileService = $this->getCloudFileService();
        $result = ReflectionUtils::invokeMethod($cloudFileService, 'filterConditions', array($conditions));
        $this->assertArraySubset($result, $conditions);
    }

    public function testFindGlobalIdsByTags()
    {
        $conditions = array(
            'tags' => 'tag1,tag2'
        );
        $mockFiles = array(
            array('fileId' => 1),
            array('fileId' => 2),
            array('fileId' => 3),
        );
        $this->mockBiz('File:UploadFileTagService', array(
            array('functionName' => 'findByTagId', 'withParams' => array($conditions['tags']), 'returnValue' => $mockFiles)
        ));

        $cloudFileService = $this->getCloudFileService();
        ReflectionUtils::invokeMethod($cloudFileService, 'findGlobalIdsByTags', array(&$conditions));
        $this->assertEquals(array_column($mockFiles, 'fileId'), $conditions['ids']);
    }

    public function testFindGlobalIdsByKeyWordsWithCourseType()
    {
        $conditions = array(
            'searchType' => 'course',
            'keywords' => 'asdasdasd'
        );

        // not find courses
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'findCourseSetsLikeTitle', 'withParams' => array($conditions['keywords']), 'returnValue' => array())
        ));
        $cloudFileService = $this->getCloudFileService();
        ReflectionUtils::invokeMethod($cloudFileService, 'findGlobalIdsByKeyWords', array(&$conditions));
        $this->assertEquals(array(-1), $conditions['ids']);


        $conditions2 = array(
            'searchType' => 'course',
            'keywords' => 'asdasdasd'
        );
        // find courses
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'findCourseSetsLikeTitle', 'withParams' => array($conditions2['keywords']), 'returnValue' => array(array('id' => 1)))
        ));
        $this->mockBiz('Course:MaterialService', array(
            array('functionName' => 'searchMaterials', 'returnValue' => array(array('fileId' => 99), array('fileId' => 100)))
        ));
        $cloudFileService = $this->getCloudFileService();
        ReflectionUtils::invokeMethod($cloudFileService, 'findGlobalIdsByKeyWords', array(&$conditions2));
        $this->assertEquals(array(99, 100), $conditions2['ids']);
    }

    public function testFindGlobalIdsByKeyWordsWithUserType()
    {
        $conditions = array(
            'searchType' => 'user',
            'keywords' => 'asdasdasd'
        );

        // not find courses
        $this->mockBiz('User:UserService', array(
            array('functionName' => 'searchUsers', 'returnValue' => array(array('id' => 1)))
        ));
        $cloudFileService = $this->getCloudFileService();
        ReflectionUtils::invokeMethod($cloudFileService, 'findGlobalIdsByKeyWords', array(&$conditions));
        $this->assertEquals(array(1), $conditions['createdUserIds']);
    }

    public function testFindGlobalIdsByKeyWordsWithOtherType()
    {
        $conditions = array(
            'searchType' => 'title',
            'keywords' => 'asdasdasd'
        );

        $cloudFileService = $this->getCloudFileService();
        ReflectionUtils::invokeMethod($cloudFileService, 'findGlobalIdsByKeyWords', array(&$conditions));
        $this->assertEquals('asdasdasd', $conditions['filename']);
    }

    /**
     * @return \Biz\CloudFile\Service\CloudFileService
     */
    private function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }
}