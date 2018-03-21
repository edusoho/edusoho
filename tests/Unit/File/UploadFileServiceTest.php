<?php

namespace Tests\Unit\File;

use Biz\File\Service\UploadFileService;
use Biz\BaseTestCase;
use Biz\User\Service\UserService;

class UploadFileServiceTest extends BaseTestCase
{
    public function testGetFile()
    {
        $fileId = 1;

        $params = array(
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => array(1),
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => array(
                    array(
                        'id' => 1,
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ),
                ),
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $file = $this->getUploadFileService()->getFile($fileId);

        $this->assertEquals($file['id'], $fileId);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:FileUploadFileDao']);
    }

    public function testGetAudioServiceStatus()
    {
        $settingParams = array(
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => array(
                    'cloud_access_key' => 'abbbcc',
                    'cloud_secret_key' => 'testddd',
                ),
            ),
        );
        $this->mockBiz('System:SettingService', $settingParams);

        $params = array(
            array(
                'functionName' => 'getAudioServiceStatus',
                'runTimes' => 1,
                'returnValue' => array(
                    'audioService' => 'opened',
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $status = $this->getUploadFileService()->getAudioServiceStatus();
        $this->assertEquals($status, 'opened');
    }

    public function testGetFullFile()
    {
        $fileId = 1;

        $params = array(
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => array(1),
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'globalId' => '535399bd5f19413c9339a5ab11c3a5d1',
                ),
            ),
        );

        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $file = $this->getUploadFileService()->getFullFile($fileId);

        $this->assertEquals($file['id'], $fileId);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testBatchConvertByIdsWithEmpty()
    {
        $ids = array();

        $result = $this->getUploadFileService()->batchConvertByIds($ids);

        $this->assertEquals($result, false);
    }

    public function testBatchConvertByIds()
    {
        $ids = array(1, 2);

        $this->mockBiz('File:UploadFileDao');
        $this->getUploadFileDao()->shouldReceive('count')->andReturn(2);

        $this->getUploadFileDao()->shouldReceive('search')->andReturn(array(
            array(
                'id' => 1,
                'globalId' => 'edc25dbecfee41cf9726882535995ac3',
            ),
            array(
                'id' => 2,
                'globalId' => 'a6d07c5223fc4976bec4d842709e0a8b',
            ),
        ));

        $params = array(
            array(
                'functionName' => 'retryTranscode',
                'runTimes' => 1,
                'returnValue' => true,
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);
        $this->getUploadFileDao()->shouldReceive('update');

        $result = $this->getUploadFileService()->batchConvertByIds($ids);

        $this->assertEquals($result, true);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetAudioConvertionStatusWithEmpty()
    {
        $ids = array();
        $result = $this->getUploadFileService()->getAudioConvertionStatus($ids);

        $this->assertEquals($result, '0');
    }

    public function testGetAudioConvertionStatus()
    {
        $ids = array(1, 2, 3, 4);

        $params = array(
            array(
                'functionName' => 'count',
                'runTimes' => 1,
                'returnValue' => 2,
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->getAudioConvertionStatus($ids);

        $this->assertEquals($result, '2/4');

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetResourcesStatus()
    {
        $params = array(
            array(
                'functionName' => 'getResourcesStatus',
                'runTimes' => 1,
                'returnValue' => array(
                    array(
                        'data' => array(
                            'resourceNo' => '65d474f089074fa0810d1f2f146fd218',
                            'status' => 'ok',
                            'mp4' => false,
                            'audio' => true,
                        ),
                        'next' => array(
                            'cursor' => '1519214541',
                            'start' => 0,
                            'limit' => 1,
                        ),
                    ),
                ),
            ),
        );

        $this->mockBiz('File:CloudFileImplementor', $params);

        $globalId = '65d474f089074fa0810d1f2f146fd218';
        $status = $this->getUploadFileService()->getResourcesStatus(array('cursor' => 0, 'start' => 0, 'limit' => 2));

        $this->assertEquals($globalId, $status[0]['data']['resourceNo']);
        $this->assertEquals('ok', $status[0]['data']['status']);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
    }

    public function testGetFileByGlobalId()
    {
        $params = array(
            array(
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'withParams' => array('65d474f089074fa0810d1f2f146fd218'),
                'returnValue' => array(
                    'id' => 1,
                    'globalId' => '65d474f089074fa0810d1f2f146fd218',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => array(),
                'returnValue' => array(
                    'id' => 1,
                    'globalId' => '65d474f089074fa0810d1f2f146fd218',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = array(
            array(
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => array(),
                'returnValue' => array(
                    'id' => 2,
                    'globalId' => 0,
                    'storage' => 'local',
                    'filename' => 'localFileName',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:LocalFileImplementor', $params);

        $params = array(
            array(
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'withParams' => array(),
                'returnValue' => array(
                    'id' => 1,
                    'globalId' => '65d474f089074fa0810d1f2f146fd218',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $globalId = '65d474f089074fa0810d1f2f146fd218';
        $cloudFile = $this->getUploadFileService()->getFileByGlobalId($globalId);

        $this->assertEquals($globalId, $cloudFile['globalId']);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:LocalFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetFileByHashId()
    {
        $hashId = 'materiallib-1/20160418040438-d11n060aceo8g8ws';

        $params = array(
            array(
                'functionName' => 'getByHashId',
                'runTimes' => 1,
                'withParams' => array('materiallib-1/20160418040438-d11n060aceo8g8ws'),
                'returnValue' => array(
                    'id' => 1,
                    'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => array(
                    array(
                        'id' => 1,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ),
                ),
                'returnValue' => array(
                    'id' => 1,
                    'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $file = $this->getUploadFileService()->getFileByHashId($hashId);
        $this->assertEquals($file['hashId'], $hashId);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetFileByConvertHash()
    {
        $hash = 'materiallib-1/20160418040438-d11n060aceo8g8ws';

        $params = array(
            array(
                'functionName' => 'getByConvertHash',
                'runTimes' => 1,
                'withParams' => array('materiallib-1/20160418040438-d11n060aceo8g8ws'),
                'returnValue' => array(
                    'id' => 1,
                    'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);
        $file = $this->getUploadFileService()->getFileByConvertHash($hash);

        $this->assertEquals($file['convertHash'], $hash);

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testFindFilesByIds()
    {
        $ids = array(1, 2);

        $params = array(
            array(
                'arguments' => true,
                'functionName' => 'findByIds',
                'runTimes' => 1,
                'withParams' => array(array(1, 2)),
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ),
                    array(
                        'id' => 2,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ),
                ),
            ),
        );

        $this->mockBiz('File:UploadFileDao', $params);

        $files = $this->getUploadFileService()->findFilesByIds($ids, false);

        $this->assertEquals($files[0]['id'], 1);
        $this->assertEquals($files[1]['id'], 2);

        //@TODO biz 每次单元测试不会重置mock dao后只能暂时先这么处理
        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    // 单元测试有概率会挂，注释掉
    // public function testSearchFiles()
    // {
    //     $conditions = array(
    //         'source' => 'shared',
    //         'currentUserId' => 1,
    //     );
    //     $withConditions = array(
    //         'source' => 'shared',

    //         'currentUserIds' => array(
    //             1,
    //             2,
    //         ),
    //         'currentUserId' => 1,
    //     );

    //     $orderBy = array('createdTime', 'DESC');
    //     $start = 0;
    //     $limit = 20;

    //     $params = array(
    //         array(
    //             'functionName' => 'search',
    //             'runTimes' => 1,
    //             'withParams' => array($withConditions, $orderBy, $start, $limit),
    //             'returnValue' => array(
    //                 array(
    //                     'id' => 1,
    //                     'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
    //                     'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
    //                     'storage' => 'cloud',
    //                     'filename' => 'test',
    //                     'createdUserId' => 1,
    //                 ),
    //                 array(
    //                     'id' => 2,
    //                     'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
    //                     'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
    //                     'storage' => 'cloud',
    //                     'filename' => 'test',
    //                     'createdUserId' => 1,
    //                 ),
    //             ),
    //         ),
    //     );
    //     $this->mockBiz('File:UploadFileDao', $params);

    //     $params = array(
    //         array(
    //             'functionName' => 'findByTargetUserIdAndIsActive',
    //             'runTimes' => 1,
    //             'withParams' => array(1),
    //             'returnValue' => array(
    //                 array(
    //                     'id' => 1,
    //                     'sourceUserId' => 1,
    //                     'targetUserId' => 2,
    //                     'isActive' => 1,
    //                     'createdTime' => 1461037751,
    //                     'updatedTime' => 1461037751,
    //                 ),
    //                 array(
    //                     'id' => 2,
    //                     'sourceUserId' => 2,
    //                     'targetUserId' => 3,
    //                     'isActive' => 1,
    //                     'createdTime' => 1461037751,
    //                     'updatedTime' => 1461037751,
    //                 ),
    //             ),
    //         ),
    //     );
    //     $this->mockBiz('File:UploadFileShareDao', $params);

    //     $files = $this->getUploadFileService()->searchFiles($conditions, $orderBy, $start, $limit);
    //     $this->assertEquals($files[0]['id'], 1);
    //     $this->assertEquals($files[1]['id'], 2);

    //     $biz = $this->getBiz();
    //     unset($biz['@File:UploadFileDao']);
    //     unset($biz['@File:UploadFileShareDao']);
    // }

    public function testSearchFileCount()
    {
        $conditions = array(
            'source' => 'shared',
            'currentUserId' => 1,
        );

        $params = array(
            array(
                'functionName' => 'findByTargetUserIdAndIsActive',
                'runTimes' => 1,
                'withParams' => array(1),
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'sourceUserId' => 2,
                        'targetUserId' => 1,
                        'isActive' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ),
                    array(
                        'id' => 2,
                        'sourceUserId' => 3,
                        'targetUserId' => 1,
                        'isActive' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ),
                ),
            ),
        );
        $this->mockBiz('File:UploadFileShareDao', $params);

        $params = array(
            array(
                'functionName' => 'findByUserId',
                'runTimes' => 1,
                'withParams' => array(1),
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'fileId' => 1,
                        'userId' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ),
                    array(
                        'id' => 2,
                        'fileId' => 2,
                        'userId' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ),
                ),
            ),
        );
        $this->mockBiz('File:UploadFileCollectDao', $params);

        $params = array(
            array(
                'functionName' => 'count',
                'runTimes' => 1,
                'returnValue' => 2,
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);
        $count = $this->getUploadFileService()->searchFileCount($conditions);
        $this->assertEquals($count, 2);

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
        unset($biz['@File:UploadFileCollectDao']);
        unset($biz['@File:UploadFileShareDao']);
    }

    public function testAddFile()
    {
        $params = array(
            array(
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'targetType' => 'materiallib',
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'addFile',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'targetType' => 'materiallib',
                ),
            ),
        );
        $this->mockBiz('File:LocalFileImplementor', $params);
        $file = $this->getUploadFileService()->addFile('materiallib', 1);
        $this->assertEquals($file['id'], 1);

        $biz = $this->getBiz();
        unset($biz['@File:LocalFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testRenameFile()
    {
        $id = 1;
        $newFileName = 'test2';

        $params = array(
            array(
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test2',
                    'createdUserId' => 1,
                ),
            ),
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'convertParams' => null,
                    'metas' => null,
                    'metas2' => null,
                    'storage' => 'cloud',
                    'filename' => 'test2',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $file = $this->getUploadFileService()->renameFile($id, $newFileName);
        $this->assertEquals($file['filename'], 'test2');

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testDeleteFile()
    {
        $id = 1;

        $params = array(
            array(
                'functionName' => 'delete',
                'runTimes' => 1,
                'returnValue' => true,
            ),
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'deleteFile',
                'runTimes' => 1,
                'returnValue' => true,
            ),
            array(
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->deleteFile($id);
        $this->assertEquals($result, true);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testFindMySharingContacts()
    {
        $user1 = $this->createUser('111111');
        $user2 = $this->createUser('222222');
        $user3 = $this->createUser('333333');

        $fileShare1 = $this->getUploadFileService()->addShare($user1['id'], $user2['id']);
        $fileShare2 = $this->getUploadFileService()->addShare($user1['id'], $user3['id']);
        $fileShare3 = $this->getUploadFileService()->addShare($user2['id'], $user3['id']);

        $sourceUsers = $this->getUploadFileService()->findMySharingContacts($user3['id']);

        $this->assertArrayHasKey($user1['id'], $sourceUsers);
        $this->assertArrayHasKey($user2['id'], $sourceUsers);
    }

    public function testShareFiles()
    {
        $this->getUploadFileService()->shareFiles(1, array(2, 3, 4));
        $userShare = $this->getUploadFileService()->findShareHistoryByUserId(1, 3);

        $this->assertEquals(1, $userShare['sourceUserId']);
        $this->assertEquals(3, $userShare['targetUserId']);
    }

    public function testAddShare()
    {
        $sourceUserId = 1;
        $targetUserId = 2;

        $fileShare = $this->getUploadFileService()->addShare($sourceUserId, $targetUserId);

        $this->assertEquals($sourceUserId, $fileShare['sourceUserId']);
        $this->assertEquals($targetUserId, $fileShare['targetUserId']);
    }

    public function testUpdateShare()
    {
        $sourceUserId = 1;
        $targetUserId = 2;
        $fileShare = $this->getUploadFileService()->addShare($sourceUserId, $targetUserId);

        $updateFileShare = $this->getUploadFileService()->updateShare($fileShare['id']);
        $this->assertEquals($sourceUserId, $updateFileShare['sourceUserId']);
        $this->assertEquals($targetUserId, $updateFileShare['targetUserId']);
        $this->assertEquals(1, $updateFileShare['isActive']);
    }

    public function testFindShareHistoryByUserId()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);

        $userSharefile = $this->getUploadFileService()->findShareHistoryByUserId(1, 2);

        $this->assertEquals(1, $userSharefile['sourceUserId']);
        $this->assertEquals(2, $userSharefile['targetUserId']);
    }

    public function testFindShareHistory()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareFiles = $this->getUploadFileService()->findShareHistory(1);

        $this->assertEquals(2, count($shareFiles));
    }

    public function testFindActiveShareHistory()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $fileShares = $this->getUploadFileService()->findActiveShareHistory(1);

        $this->assertEquals(2, count($fileShares));
    }

    public function testCancelShareFile()
    {
        $fileShare = $this->getUploadFileService()->addShare(1, 2);
        $fileShare = $this->getUploadFileService()->cancelShareFile(1, 2);

        $this->assertEquals(0, $fileShare['isActive']);
    }

    public function testSearchShareHistoryCount()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareCount = $this->getUploadFileService()->searchShareHistoryCount(array('sourceUserId' => 1, 'isActive' => 1));

        $this->assertEquals(2, $shareCount);
    }

    public function testSearchShareHistories()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareFiles = $this->getUploadFileService()->searchShareHistories(
            array(
            'sourceUserId' => 1,
        ),
            array('createdTime' => 'DESC'),
            0,
            10
        );

        $this->assertEquals(2, count($shareFiles));
    }

    public function testWaveUploadFile()
    {
        $params = array(
            array(
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'usedCount' => 0,
                    'targetType' => 'materiallib',
                ),
            ),
            array(
                'functionName' => 'waveUsedCount',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'usedCount' => 1,
                ),
            ),
        );
        $this->mockBiz('File:UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'addFile',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ),
            ),
        );
        $this->mockBiz('File:LocalFileImplementor', $params);

        $file = $this->getUploadFileService()->addFile('materiallib', 1);

        $updateFile = $this->getUploadFileService()->waveUsedCount($file['id'], +1);

        $this->assertEquals($file['usedCount'] + 1, $updateFile['usedCount']);

        $biz = $this->getBiz();
        unset($biz['@File:LocalFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testSearchCloudFilesFromLocal()
    {
        $this->mockBiz(
            'File:UploadFileDao',
            array(
                array(
                    'functionName' => 'search',
                    'withParams' => array(array(), array(), 0, 5),
                    'returnValue' => array(),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(array('resType' => 'attachment'), array(), 0, 5),
                    'returnValue' => array(
                        array(
                            'id' => 11,
                            'type' => 'other',
                        ),
                    ),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'File:CloudFileImplementor',
            array(
                array(
                    'functionName' => 'findFiles',
                    'withParams' => array(array(array('id' => 11, 'type' => 'other')), array('resType' => 'attachment')),
                    'returnValue' => array(array('id' => 11)),
                ),
            )
        );
        $result = $this->getUploadFileService()->searchCloudFilesFromLocal(array(), array(), 0, 5);
        $this->assertEquals(array(), $result);

        $result = $result = $this->getUploadFileService()->searchCloudFilesFromLocal(array('resType' => 'attachment'), array(), 0, 5);
        $this->assertEquals(array(array('id' => 11, 'type' => 'other')), $result);
    }

    public function testcountCloudFilesFromLocal()
    {
        $this->mockBiz(
            'File:UploadFileDao',
            array(
                array(
                    'functionName' => 'count',
                    'withParams' => array(array()),
                    'returnValue' => 1,
                    'runTimes' => 1,
                ),
            )
        );
        $result = $this->getUploadFileService()->countCloudFilesFromLocal(array());
        $this->assertEquals(1, $result);
    }

    protected function createUser($user)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';

        return $this->getUserService()->register($userInfo);
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }
}
