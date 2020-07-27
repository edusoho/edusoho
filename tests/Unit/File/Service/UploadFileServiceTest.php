<?php

namespace Tests\Unit\File\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\File\Service\UploadFileService;
use Biz\User\Service\UserService;

class UploadFileServiceTest extends BaseTestCase
{
    public function testGetFile()
    {
        $fileId = 1;

        $params = [
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [
                    [
                        'id' => 1,
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ],
                ],
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $file = $this->getUploadFileService()->getFile($fileId);

        $this->assertEquals($file['id'], $fileId);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:FileUploadFileDao']);
    }

    public function testGetAudioServiceStatus()
    {
        $settingParams = [
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => [
                    'cloud_access_key' => 'abbbcc',
                    'cloud_secret_key' => 'testddd',
                ],
            ],
        ];
        $this->mockBiz('System:SettingService', $settingParams);

        $params = [
            [
                'functionName' => 'getAudioServiceStatus',
                'runTimes' => 1,
                'returnValue' => [
                    'audioService' => 'opened',
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $status = $this->getUploadFileService()->getAudioServiceStatus();
        $this->assertEquals($status, 'opened');
    }

    public function testGetAudioServiceStatusWithEmptyAudioService()
    {
        $settingParams = [
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => [
                    'cloud_access_key' => 'abbbcc',
                    'cloud_secret_key' => 'testddd',
                ],
            ],
        ];
        $this->mockBiz('System:SettingService', $settingParams);

        $params = [
            [
                'functionName' => 'getAudioServiceStatus',
                'runTimes' => 1,
                'returnValue' => [
                    'audioService' => '',
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $status = $this->getUploadFileService()->getAudioServiceStatus();
        $this->assertEquals($status, 'notAllowed');
    }

    public function testGetAudioServiceStatusWithNeedOpen()
    {
        $settingParams = [
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => [
                    'cloud_access_key' => 'abbbcc',
                    'cloud_secret_key' => 'testddd',
                ],
            ],
        ];
        $this->mockBiz('System:SettingService', $settingParams);

        $params = [
            [
                'functionName' => 'getAudioServiceStatus',
                'runTimes' => 1,
                'returnValue' => [
                    'audioService' => 'opened',
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'functionName' => 'isSupportEnableAudio',
                'runTimes' => 1,
                'returnValue' => false,
            ],
        ];
        $this->mockBiz('Course:CourseService', $params);

        $status = $this->getUploadFileService()->getAudioServiceStatus();
        $this->assertEquals($status, 'needOpen');
    }

    public function testGetFullFile()
    {
        $fileId = 1;

        $params = [
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'globalId' => '535399bd5f19413c9339a5ab11c3a5d1',
                ],
            ],
        ];

        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $file = $this->getUploadFileService()->getFullFile($fileId);

        $this->assertEquals($file['id'], $fileId);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testBatchConvertByIdsWithEmpty()
    {
        $ids = [];

        $result = $this->getUploadFileService()->batchConvertByIds($ids);

        $this->assertEquals($result, false);
    }

    public function testBatchConvertByIds()
    {
        $ids = [1, 2];

        $this->mockBiz('File:UploadFileDao');
        $this->getUploadFileDao()->shouldReceive('count')->andReturn(2);

        $this->getUploadFileDao()->shouldReceive('search')->andReturn([
            [
                'id' => 1,
                'globalId' => 'edc25dbecfee41cf9726882535995ac3',
            ],
            [
                'id' => 2,
                'globalId' => 'a6d07c5223fc4976bec4d842709e0a8b',
            ],
        ]);

        $params = [
            [
                'functionName' => 'retryTranscode',
                'runTimes' => 1,
                'returnValue' => true,
            ],
        ];
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
        $ids = [];
        $result = $this->getUploadFileService()->getAudioConvertionStatus($ids);

        $this->assertEquals($result, '0');
    }

    public function testGetUploadFileInit()
    {
        $result = $this->getUploadFileService()->getUploadFileInit(1);
        $this->assertEmpty($result);
    }

    public function testGetAudioConvertionStatus()
    {
        $ids = [1, 2, 3, 4];

        $params = [
            [
                'functionName' => 'count',
                'runTimes' => 1,
                'returnValue' => 2,
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->getAudioConvertionStatus($ids);

        $this->assertEquals($result, '2/4');

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetResourcesStatus()
    {
        $params = [
            [
                'functionName' => 'getResourcesStatus',
                'runTimes' => 1,
                'returnValue' => [
                    [
                        'data' => [
                            'resourceNo' => '65d474f089074fa0810d1f2f146fd218',
                            'status' => 'ok',
                            'mp4' => false,
                            'audio' => true,
                        ],
                        'next' => [
                            'cursor' => '1519214541',
                            'start' => 0,
                            'limit' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $this->mockBiz('File:CloudFileImplementor', $params);

        $globalId = '65d474f089074fa0810d1f2f146fd218';
        $status = $this->getUploadFileService()->getResourcesStatus(['cursor' => 0, 'start' => 0, 'limit' => 2]);

        $this->assertEquals($globalId, $status[0]['data']['resourceNo']);
        $this->assertEquals('ok', $status[0]['data']['status']);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
    }

    public function testGetFileByGlobalId()
    {
        $params = [
            [
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'withParams' => ['65d474f089074fa0810d1f2f146fd218'],
                'returnValue' => [
                    'id' => 1,
                    'globalId' => '65d474f089074fa0810d1f2f146fd218',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [],
                'returnValue' => [
                    'id' => 1,
                    'globalId' => '65d474f089074fa0810d1f2f146fd218',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [],
                'returnValue' => [
                    'id' => 2,
                    'globalId' => 0,
                    'storage' => 'local',
                    'filename' => 'localFileName',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:LocalFileImplementor', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'withParams' => [],
                'returnValue' => [
                    'id' => 1,
                    'globalId' => '65d474f089074fa0810d1f2f146fd218',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $globalId = '65d474f089074fa0810d1f2f146fd218';
        $cloudFile = $this->getUploadFileService()->getFileByGlobalId($globalId);

        $this->assertEquals($globalId, $cloudFile['globalId']);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:LocalFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetFileByGlobalIdWithEmptyFile()
    {
        $params = [
            [
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->getFileByGlobalId(1);
        $this->assertNull($result);
    }

    public function testGetFileByHashId()
    {
        $hashId = 'materiallib-1/20160418040438-d11n060aceo8g8ws';

        $params = [
            [
                'functionName' => 'getByHashId',
                'runTimes' => 1,
                'withParams' => ['materiallib-1/20160418040438-d11n060aceo8g8ws'],
                'returnValue' => [
                    'id' => 1,
                    'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [
                    [
                        'id' => 1,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ],
                ],
                'returnValue' => [
                    'id' => 1,
                    'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $file = $this->getUploadFileService()->getFileByHashId($hashId);
        $this->assertEquals($file['hashId'], $hashId);

        $biz = $this->getBiz();
        unset($biz['@File:CloudFileImplementor']);
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetFileByHashIddWithEmptyFile()
    {
        $params = [
            [
                'functionName' => 'getByHashId',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->getFileByHashId(1);
        $this->assertNull($result);
    }

    public function testGetFileByConvertHash()
    {
        $hash = 'materiallib-1/20160418040438-d11n060aceo8g8ws';

        $params = [
            [
                'functionName' => 'getByConvertHash',
                'runTimes' => 1,
                'withParams' => ['materiallib-1/20160418040438-d11n060aceo8g8ws'],
                'returnValue' => [
                    'id' => 1,
                    'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);
        $file = $this->getUploadFileService()->getFileByConvertHash($hash);

        $this->assertEquals($file['convertHash'], $hash);

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testFindFilesByIds()
    {
        $ids = [1, 2];

        $params = [
            [
                'arguments' => true,
                'functionName' => 'findByIds',
                'runTimes' => 1,
                'withParams' => [[1, 2]],
                'returnValue' => [
                    [
                        'id' => 1,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ],
                    [
                        'id' => 2,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ],
                ],
            ],
        ];

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

    public function testFindFilesByTargetTypeAndTargetIds()
    {
        $ids = [1, 2];

        $params = [
            [
                'arguments' => true,
                'functionName' => 'findByTargetTypeAndTargetIds',
                'runTimes' => 1,
                'withParams' => ['video', [1, 2]],
                'returnValue' => [
                    [
                        'id' => 1,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ],
                    [
                        'id' => 2,
                        'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
                        'storage' => 'cloud',
                        'filename' => 'test',
                        'createdUserId' => 1,
                    ],
                ],
            ],
        ];

        $this->mockBiz('File:UploadFileDao', $params);
        $result = $this->getUploadFileService()->findFilesByTargetTypeAndTargetIds('video', $ids);

        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
    }

    public function testUpdate()
    {
        $result = $this->getUploadFileService()->update(1, []);
        $this->assertFalse($result);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [
                    'id' => 2,
                    'globalId' => 0,
                ],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => true,
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'deleteByFileId',
                'runTimes' => 1,
                'withParams' => [2],
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileTagDao', $params);
        $result = $this->getUploadFileService()->update(1, [
            'tags' => 0,
            'name' => 'name Test',
            'isPublic' => 1,
            'filename' => 'filenameTest',
            'description' => 'description',
            'targetId' => 1,
            'useType' => 'type',
            'usedCount' => 10,
        ]);
        $this->assertTrue($result);
    }

    public function testSearchFileCount()
    {
        $conditions = [
            'source' => 'shared',
            'currentUserId' => 1,
        ];

        $params = [
            [
                'functionName' => 'findByTargetUserIdAndIsActive',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [
                    [
                        'id' => 1,
                        'sourceUserId' => 2,
                        'targetUserId' => 1,
                        'isActive' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ],
                    [
                        'id' => 2,
                        'sourceUserId' => 3,
                        'targetUserId' => 1,
                        'isActive' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ],
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileShareDao', $params);

        $params = [
            [
                'functionName' => 'findByUserId',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [
                    [
                        'id' => 1,
                        'fileId' => 1,
                        'userId' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ],
                    [
                        'id' => 2,
                        'fileId' => 2,
                        'userId' => 1,
                        'createdTime' => 1461037751,
                        'updatedTime' => 1461037751,
                    ],
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileCollectDao', $params);

        $params = [
            [
                'functionName' => 'count',
                'runTimes' => 1,
                'returnValue' => 2,
            ],
        ];
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
        $params = [
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'targetType' => 'materiallib',
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'addFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'targetType' => 'materiallib',
                ],
            ],
        ];
        $this->mockBiz('File:LocalFileImplementor', $params);

        $params = [
            [
                'functionName' => 'addFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'targetType' => 'materiallib',
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);
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

        $params = [
            [
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test2',
                    'createdUserId' => 1,
                ],
            ],
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'convertParams' => null,
                    'metas' => null,
                    'metas2' => null,
                    'storage' => 'cloud',
                    'filename' => 'test2',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $file = $this->getUploadFileService()->renameFile($id, $newFileName);
        $this->assertEquals($file['filename'], 'test2');

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testDeleteFile()
    {
        $id = 1;

        $params = [
            [
                'functionName' => 'delete',
                'runTimes' => 1,
                'returnValue' => true,
            ],
            [
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'deleteFile',
                'runTimes' => 1,
                'returnValue' => true,
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
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
        $this->getUploadFileService()->shareFiles(1, [2, 3, 4]);
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

        $shareCount = $this->getUploadFileService()->searchShareHistoryCount(['sourceUserId' => 1, 'isActive' => 1]);

        $this->assertEquals(2, $shareCount);
    }

    public function testSearchShareHistories()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareFiles = $this->getUploadFileService()->searchShareHistories(
            [
            'sourceUserId' => 1,
        ],
            ['createdTime' => 'DESC'],
            0,
            10
        );

        $this->assertEquals(2, count($shareFiles));
    }

    public function testWaveUploadFile()
    {
        $params = [
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'usedCount' => 0,
                    'targetType' => 'materiallib',
                ],
            ],
            [
                'functionName' => 'waveUsedCount',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                    'usedCount' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'addFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
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
            [
                [
                    'functionName' => 'search',
                    'withParams' => [[], [], 0, 5],
                    'returnValue' => [],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'search',
                    'withParams' => [['resType' => 'attachment'], [], 0, 5],
                    'returnValue' => [
                        [
                            'id' => 11,
                            'type' => 'other',
                        ],
                    ],
                    'runTimes' => 1,
                ],
            ]
        );
        $this->mockBiz(
            'File:CloudFileImplementor',
            [
                [
                    'functionName' => 'findFiles',
                    'withParams' => [[['id' => 11, 'type' => 'other']], ['resType' => 'attachment']],
                    'returnValue' => [['id' => 11]],
                ],
            ]
        );
        $result = $this->getUploadFileService()->searchCloudFilesFromLocal([], [], 0, 5);
        $this->assertEquals([], $result);

        $result = $result = $this->getUploadFileService()->searchCloudFilesFromLocal(['resType' => 'attachment'], [], 0, 5);
        $this->assertEquals([['id' => 11, 'type' => 'other']], $result);
    }

    public function testCountCloudFilesFromLocal()
    {
        $this->mockBiz(
            'File:UploadFileDao',
            [
                [
                    'functionName' => 'count',
                    'withParams' => [[]],
                    'returnValue' => 1,
                    'runTimes' => 1,
                ],
            ]
        );
        $result = $this->getUploadFileService()->countCloudFilesFromLocal([]);
        $this->assertEquals(1, $result);
    }

    public function testSharePublic()
    {
        $file = ['id' => 1, 'isPublic' => 1];
        $this->mockBiz('File:UploadFileDao',
            [
                [
                    'functionName' => 'update',
                    'returnValue' => $file,
                    'runTimes' => 1,
                ],
            ]
        );

        $result = $this->getUploadFileService()->sharePublic($file['id']);
        $this->assertEquals($file['isPublic'], $result['isPublic']);
    }

    public function testUnsharePublic()
    {
        $file = ['id' => 1, 'isPublic' => 0];
        $this->mockBiz('File:UploadFileDao',
            [
                [
                    'functionName' => 'update',
                    'returnValue' => $file,
                    'runTimes' => 1,
                ],
            ]
        );

        $result = $this->getUploadFileService()->unsharePublic($file['id']);
        $this->assertEquals($file['isPublic'], $result['isPublic']);
    }

    public function testGetDownloadMetas()
    {
        $result = $this->getUploadFileService()->getDownloadMetas(1);
        $this->assertEquals('not_found', $result['error']);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getDownloadFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test',
                    'createdUserId' => 1,
                ],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);
        $result = $this->getUploadFileService()->getDownloadMetas(1);
        $this->assertEquals('test', $result['filename']);
    }

    public function testGetUploadAuth()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => ['storage'],
                'returnValue' => ['upload_mode' => 'cloud'],
            ],
        ];
        $this->mockBiz('System:SettingService', $params);

        $params = [
            [
                'functionName' => 'getUploadAuth',
                'runTimes' => 1,
                'returnValue' => true,
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->getUploadAuth([]);
        $this->assertTrue($result);
    }

    public function testInitFormUpload()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => ['storage'],
                'returnValue' => ['upload_mode' => 'cloud'],
            ],
        ];
        $this->mockBiz('System:SettingService', $params);

        $params = [
            [
                'functionName' => 'resumeUpload',
                'runTimes' => 1,
                'returnValue' => ['resumed' => 'ok'],
            ],
            [
                'functionName' => 'prepareUpload',
                'runTimes' => 1,
                'returnValue' => [],
            ],
            [
                'functionName' => 'initFormUpload',
                'runTimes' => 1,
                'returnValue' => ['resumed' => 'no', 'globalId' => 2],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['status' => 'notok', 'id' => 3],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileInitDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'info',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('logger', $params);

        $result = $this->getUploadFileService()->initFormUpload([
            'targetId' => 1,
            'targetType' => 'video',
            'hash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            'id' => 1,
            'fileName' => 'test',
            'fileSize' => 1024,
        ]);
        $this->assertEquals('ok', $result['resumed']);

        $result = $this->getUploadFileService()->initFormUpload([
            'targetId' => 1,
            'targetType' => 'video',
            'hash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
        ]);
        $this->assertEquals(2, $result['globalId']);
    }

    public function testInitUpload()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => ['storage'],
                'returnValue' => ['upload_mode' => 'cloud'],
            ],
        ];
        $this->mockBiz('System:SettingService', $params);

        $params = [
            [
                'functionName' => 'resumeUpload',
                'runTimes' => 1,
                'returnValue' => ['resumed' => 'ok'],
            ],
            [
                'functionName' => 'prepareUpload',
                'runTimes' => 1,
                'returnValue' => [],
            ],
            [
                'functionName' => 'initUpload',
                'runTimes' => 1,
                'returnValue' => ['resumed' => 'no', 'globalId' => 2],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['status' => 'notok', 'id' => 3],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileInitDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'info',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('logger', $params);

        $result = $this->getUploadFileService()->initUpload([
            'targetId' => 1,
            'targetType' => 'video',
            'hash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            'id' => 1,
            'fileName' => 'test',
            'fileSize' => 1024,
        ]);
        $this->assertEquals('no', $result['resumed']);

        $result = $this->getUploadFileService()->initUpload([
            'targetId' => 1,
            'targetType' => 'video',
            'hash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
        ]);
        $this->assertEquals(2, $result['globalId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testInitUploadWithErrorParam()
    {
        $this->getUploadFileService()->initUpload([]);
    }

    public function testFinishedUpload()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => ['storage'],
                'returnValue' => ['upload_mode' => 'cloud'],
            ],
        ];
        $this->mockBiz('System:SettingService', $params);

        $params = [
            [
                'functionName' => 'finishedUpload',
                'runTimes' => 1,
                'returnValue' => ['no' => '100', 'length' => 10],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'type' => 'video'],
            ],
        ];
        $this->mockBiz('File:UploadFileInitDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => ['targetType' => 'headLeader', 'id' => 4],
            ],
            [
                'arguments' => true,
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'findHeadLeaderFiles',
                'runTimes' => 1,
                'returnValue' => [['id' => 4]],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->finishedUpload([
            'size' => 1024,
            'uploadType' => 'direct',
            'id' => 4,
        ]);
        $this->assertEquals('headLeader', $result['targetType']);
        $this->assertEquals(4, $result['id']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.failed
     */
    public function testFinishedUploadWithFailedUpload()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => ['storage'],
                'returnValue' => ['upload_mode' => 'cloud'],
            ],
        ];
        $this->mockBiz('System:SettingService', $params);

        $params = [
            [
                'functionName' => 'finishedUpload',
                'runTimes' => 1,
                'returnValue' => ['no' => 0, 'length' => 10],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'type' => 'video'],
            ],
        ];
        $this->mockBiz('File:UploadFileInitDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $this->getUploadFileService()->finishedUpload([
            'size' => 1024,
            'uploadType' => 'direct',
            'id' => 4,
        ]);
    }

    public function testMoveFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'moveFile',
                'runTimes' => 1,
                'withParams' => ['video', 1, null, []],
                'returnValue' => ['success' => 0, 'length' => 10],
            ],
        ];
        $this->mockBiz('File:LocalFileImplementor', $params);

        $result = $this->getUploadFileService()->moveFile('video', 1);
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(10, $result['length']);
    }

    public function testSetFileProcessed()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $mockUploadFileInitDao = $this->mockBiz('File:UploadFileInitDao', $params);

        $this->getUploadFileService()->setFileProcessed(['globalId' => 1]);
        $mockUploadFileInitDao->shouldHaveReceived('update');
    }

    public function testDeleteByGlobalId()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'deleteByGlobalId',
                'runTimes' => 1,
                'returnValue' => true,
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->deleteByGlobalId(['globalId' => 1]);
        $this->assertTrue($result);
    }

    public function testDeleteByGLobalIdWithEmptyFile()
    {
        $params = [
            [
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->deleteByGlobalId(1);
        $this->assertNull($result);
    }

    public function testReconvertFile()
    {
        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'globalId' => 1],
            ],
            [
                'functionName' => 'reconvert',
                'runTimes' => 1,
                'returnValue' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => ['storage' => 'cloud', 'globalId' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->reconvertFile(1);
        $this->assertEquals('materiallib-1/20160418040438-d11n060aceo8g8ws', $result);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testReconvertFileWithNotfoundFile()
    {
        $this->getUploadFileService()->reconvertFile(1);
    }

    public function testReconvertOldFile()
    {
        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 1, 'storage' => 'cloud']],
                'returnValue' => [],
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 2, 'storage' => 'cloud']],
                'returnValue' => ['storage' => 'local'],
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 3, 'storage' => 'cloud']],
                'returnValue' => ['storage' => 'cloud', 'type' => 'audio'],
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 4, 'storage' => 'cloud']],
                'returnValue' => ['storage' => 'cloud', 'type' => 'video', 'targetType' => 'classroom'],
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 5, 'storage' => 'cloud']],
                'returnValue' => ['storage' => 'cloud', 'type' => 'video', 'targetType' => 'courselesson', 'targetId' => 1],
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 6, 'storage' => 'cloud']],
                'returnValue' => [
                    'storage' => 'cloud',
                    'type' => 'video',
                    'targetType' => 'courselesson',
                    'targetId' => 2,
                    'convertParams' => ['convertor' => 'HLSEncryptedVideo'],
                ],
            ],
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'withParams' => [['id' => 7, 'storage' => 'cloud']],
                'returnValue' => [
                    'id' => 11,
                    'storage' => 'cloud',
                    'type' => 'video',
                    'targetType' => 'courselesson',
                    'targetId' => 2,
                    'convertParams' => ['convertor' => 'HLSVideo', 'videoQuality' => 'low'],
                ],
            ],
            [
                'functionName' => 'reconvertOldFile',
                'runTimes' => 1,
                'returnValue' => '65d474f089074fa0810d1f2f146fd218',
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [2],
                'returnValue' => ['id' => 2, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [3],
                'returnValue' => ['id' => 3, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [4],
                'returnValue' => ['id' => 4, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [5],
                'returnValue' => ['id' => 5, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [6],
                'returnValue' => ['id' => 6, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'withParams' => [7],
                'returnValue' => ['id' => 7, 'storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'getCourse',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [],
            ],
            [
                'arguments' => true,
                'functionName' => 'getCourse',
                'runTimes' => 1,
                'withParams' => [2],
                'returnValue' => ['id' => 2],
            ],
        ];
        $this->mockBiz('Course:CourseService', $params);

        $result = $this->getUploadFileService()->reconvertOldFile(1, null, null);
        $this->assertEquals('file_not_found', $result['error']);
        $this->assertEquals('文件1，不存在。', $result['message']);
        $result = $this->getUploadFileService()->reconvertOldFile(2, null, null);
        $this->assertEquals('not_cloud_file', $result['error']);
        $this->assertEquals('文件2，不是云文件。', $result['message']);
        $result = $this->getUploadFileService()->reconvertOldFile(3, null, null);
        $this->assertEquals('not_video_file', $result['error']);
        $this->assertEquals('文件3，不是视频文件。', $result['message']);
        $result = $this->getUploadFileService()->reconvertOldFile(4, null, null);
        $this->assertEquals('not_course_file', $result['error']);
        $this->assertEquals('文件4，不是课时文件。', $result['message']);
        $result = $this->getUploadFileService()->reconvertOldFile(5, null, null);
        $this->assertEquals('course_not_exist', $result['error']);
        $this->assertEquals('文件5所属的课程已删除。', $result['message']);
        $result = $this->getUploadFileService()->reconvertOldFile(6, null, null);
        $this->assertEquals('already_converted', $result['error']);
        $this->assertEquals('文件6已转换', $result['message']);
        $result = $this->getUploadFileService()->reconvertOldFile(7, null, null);
        $this->assertEmpty($result);
    }

    public function testReconvertOldFile2()
    {
        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 11,
                    'storage' => 'cloud',
                    'type' => 'video',
                    'targetType' => 'courselesson',
                    'targetId' => 2,
                    'convertParams' => [],
                ],
            ],
            [
                'functionName' => 'reconvertOldFile',
                'runTimes' => 1,
                'returnValue' => null,
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['id' => 8, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'getCourse',
                'runTimes' => 1,
                'withParams' => [2],
                'returnValue' => ['id' => 2],
            ],
        ];
        $this->mockBiz('Course:CourseService', $params);

        $result = $this->getUploadFileService()->reconvertOldFile(8, null, null);
        $this->assertEquals('convert_request_failed', $result['error']);
        $this->assertEquals('文件8转换请求失败！', $result['message']);
    }

    public function testCollectFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'getByUserIdAndFileId',
                'runTimes' => 1,
                'withParams' => [2, 2],
                'returnValue' => [],
            ],
            [
                'arguments' => true,
                'functionName' => 'getByUserIdAndFileId',
                'runTimes' => 1,
                'withParams' => [1, 1],
                'returnValue' => ['fileId' => 1, 'id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => ['fileId' => 2],
            ],
            [
                'arguments' => true,
                'functionName' => 'delete',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileCollectDao', $params);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => true,
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->collectFile(2, 2);
        $this->assertTrue($result);
        $result = $this->getUploadFileService()->collectFile(1, 1);
        $this->assertFalse($result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testCollectFileWithErrorParams()
    {
        $this->getUploadFileService()->collectFile(0, 0);
    }

    public function testFindCollectionsByUserIdAndFileIds()
    {
        $result = $this->getUploadFileService()->findCollectionsByUserIdAndFileIds([], 1);
        $this->assertEmpty($result);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'findByUserIdAndFileIds',
                'runTimes' => 1,
                'returnValue' => [['id' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileCollectDao', $params);

        $result = $this->getUploadFileService()->findCollectionsByUserIdAndFileIds([[1]], 1);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testFindCollectionsByUserId()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'findByUserId',
                'runTimes' => 1,
                'returnValue' => [['id' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileCollectDao', $params);

        $result = $this->getUploadFileService()->findCollectionsByUserId(1);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testSyncFile()
    {
        $result = $this->getUploadFileService()->syncFile(['id' => 11]);
        $this->assertEquals(11, $result['id']);
    }

    public function testSearchFiles()
    {
        $result = $this->getUploadFileService()->searchFiles([], ['createdTime' => 'DESC'], 0, 10);
        $this->assertEmpty($result);
    }

    public function testSearchUploadFiles()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'returnValue' => [['id' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $result = $this->getUploadFileService()->searchUploadFiles([], 'lastest', 0, 10);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testCountUploadFiles()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'count',
                'runTimes' => 1,
                'returnValue' => 10,
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $count = $this->getUploadFileService()->countUploadFiles([]);
        $this->assertEquals(10, $count);
    }

    public function testSearchFilesFromCloud()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'withParams' => [[], 'created', 0, PHP_INT_MAX],
                'returnValue' => [[]],
            ],
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'withParams' => [['processStatus' => 'draft', 'errorType' => '', 'resType' => ''], 'lastest', 0, PHP_INT_MAX],
                'returnValue' => [['globalId' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'search',
                'runTimes' => 1,
                'returnValue' => ['data' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);
        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'searchFilesFromCloud', [[], 'created', 0, PHP_INT_MAX]);
        $this->assertEmpty($result);
        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'searchFilesFromCloud', [
            ['processStatus' => 'draft', 'errorType' => '', 'resType' => ''], 'lastest', 0, PHP_INT_MAX,
        ]);
        $this->assertEquals('cloud', $result);
    }

    public function testSearchFilesFromLocal()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'withParams' => [[], 'created', 0, PHP_INT_MAX],
                'returnValue' => [],
            ],
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'withParams' => [['processStatus' => 'draft', 'errorType' => '', 'resType' => ''], 'lastest', 0, PHP_INT_MAX],
                'returnValue' => [['storage' => 'cloud', 'globalId' => 1, 'id' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'findFiles',
                'runTimes' => 1,
                'returnValue' => [['id' => 1]],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);
        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'searchFilesFromLocal', [[], 'created', 0, PHP_INT_MAX]);
        $this->assertEmpty($result);
        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'searchFilesFromLocal', [
            ['processStatus' => 'draft', 'errorType' => '', 'resType' => ''], 'lastest', 0, PHP_INT_MAX,
        ]);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testSearchFileCountFromCloud()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'withParams' => [[], ['createdTime' => 'DESC'], 0, PHP_INT_MAX],
                'returnValue' => [[]],
            ],
            [
                'arguments' => true,
                'functionName' => 'search',
                'runTimes' => 1,
                'withParams' => [['processStatus' => 'draft', 'errorType' => '', 'resType' => ''], ['createdTime' => 'DESC'], 0, PHP_INT_MAX],
                'returnValue' => [['globalId' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'search',
                'runTimes' => 1,
                'returnValue' => ['count' => 11],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);
        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'searchFileCountFromCloud', [[]]);
        $this->assertEmpty($result);
        $count = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'searchFileCountFromCloud', [
            ['processStatus' => 'draft', 'errorType' => '', 'resType' => ''],
        ]);
        $this->assertEquals(11, $count);
    }

    public function testDeleteFiles()
    {
        $this->getUploadFileService()->deleteFiles([1]);
    }

    public function testSaveConvertResult()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
            [
                'functionName' => 'saveConvertResult',
                'runTimes' => 1,
                'returnValue' => ['convertStatus' => 'draft', 'metas2' => []],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->saveConvertResult(1);
        $this->assertEquals('cloud', $result['storage']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testSaveConvertResultWithEmptyFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $this->getUploadFileService()->saveConvertResult(1);
    }

    public function testSaveConvertResult3()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
            [
                'functionName' => 'saveConvertResult',
                'runTimes' => 1,
                'returnValue' => ['convertStatus' => 'success', 'metas2' => [], 'convertParams' => []],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->saveConvertResult3(1);
        $this->assertEquals('cloud', $result['storage']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testSaveConvertResult3WithEmptyFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $this->getUploadFileService()->saveConvertResult3(1);
    }

    public function testConvertFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
            [
                'functionName' => 'convertFile',
                'runTimes' => 1,
                'returnValue' => ['convertStatus' => 'draft', 'metas2' => []],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->convertFile(1, 'doing');
        $this->assertEquals('cloud', $result['storage']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.error_status
     */
    public function testConvertFileWithErrorStatus()
    {
        $this->getUploadFileService()->convertFile(1, '');
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testConvertFileWithEmptyFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $this->getUploadFileService()->convertFile(1, 'none');
    }

    public function testSetFileConverting()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->setFileConverting(1, 'waiting');
        $this->assertEquals('cloud', $result['storage']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testSetFileConvertingWithEmptyFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $this->getUploadFileService()->setFileConverting(1, '');
    }

    public function testSetAudioConvertStatus()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->setAudioConvertStatus(1, 'doing');
        $this->assertEquals('cloud', $result['storage']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.error_status
     */
    public function testSetAudioConvertStatusWithErrorStatus()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $this->getUploadFileService()->setAudioConvertStatus(1, '');
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testSetAudioConvertStatusWithEmptyFile()
    {
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $this->getUploadFileService()->setAudioConvertStatus(1, 'none');
    }

    public function testSetResourceConvertStatus()
    {
        $result = $this->getUploadFileService()->setResourceConvertStatus(1, []);
        $this->assertEmpty($result);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => ['audioConvertStatus' => 'none'],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->setResourceConvertStatus(1, ['status' => 'error', 'errorType' => 'client', 'audio' => 1, 'mp4' => 1]);
        $this->assertEquals('none', $result['audioConvertStatus']);
    }

    public function testMakeUploadParams()
    {
        $params = [
            [
                'functionName' => 'makeUploadParams',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->makeUploadParams(['storage' => 'cloud']);
        $this->assertEquals('cloud', $result['storage']);
    }

    public function testGetFileByTargetType()
    {
        $result = $this->getUploadFileService()->getFileByTargetType('video');
        $this->assertNull($result);

        $params = [
            [
                'arguments' => true,
                'functionName' => 'getByTargetType',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->getFileByTargetType('audio');
        $this->assertEquals('cloud', $result['storage']);
    }

    public function testTryManageFile()
    {
        $user = $this->getCurrentUser();
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud', 'createdUserId' => $user['id']],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->tryManageFile(1);
        $this->assertEquals('cloud', $result['storage']);
    }

    public function testTryManageGlobalFile()
    {
        $user = $this->getCurrentUser();
        $params = [
            [
                'arguments' => true,
                'functionName' => 'getByGlobalId',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud', 'createdUserId' => $user['id']],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->tryManageGlobalFile(1);
        $this->assertEquals('cloud', $result['storage']);
    }

    public function testTryAccessFile()
    {
        $user = $this->getCurrentUser();
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
            [
                'arguments' => true,
                'functionName' => 'findByUserId',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud', 'createdUserId' => $user['id']],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->tryAccessFile(1);
        $this->assertEquals('cloud', $result['storage']);
    }

    public function testCanManageFile()
    {
        $user = $this->getCurrentUser();
        $params = [
            [
                'arguments' => true,
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => ['storage' => 'cloud', 'id' => 1],
            ],
        ];
        $this->mockBiz('File:UploadFileDao', $params);

        $params = [
            [
                'functionName' => 'getFullFile',
                'runTimes' => 1,
                'returnValue' => ['id' => 1, 'storage' => 'cloud', 'createdUserId' => $user['id']],
            ],
        ];
        $this->mockBiz('File:CloudFileImplementor', $params);

        $result = $this->getUploadFileService()->canManageFile(1);
        $this->assertTrue($result);
    }

    public function testUpdateTags()
    {
        $params = [
            [
                'functionName' => 'deleteByFileId',
                'runTimes' => 1,
                'returnValue' => [],
            ],
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => [],
            ],
        ];
        $this->mockBiz('File:UploadFileTagDao', $params);

        $params = [
            [
                'functionName' => 'getTagByName',
                'runTimes' => 1,
                'returnValue' => ['id' => 1],
            ],
        ];
        $this->mockBiz('Taxonomy:TagService', $params);
        ReflectionUtils::invokeMethod($this->getUploadFileService(), 'updateTags', [['id' => 1], ['tags' => 'name,name1']]);
    }

    public function testFilterKeyWords()
    {
        $params = [
            [
                'functionName' => 'findCourseSetsLikeTitle',
                'runTimes' => 1,
                'returnValue' => [['id' => 1]],
            ],
        ];
        $this->mockBiz('Course:CourseSetService', $params);

        $params = [
            [
                'functionName' => 'searchMaterials',
                'runTimes' => 1,
                'returnValue' => [['fileId' => 1]],
            ],
        ];
        $this->mockBiz('Course:MaterialService', $params);

        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'filterKeyWords', [[
            'keywordType' => 'course',
            'keyword' => 'keyword',
        ]]);
        $this->assertEquals(1, $result['ids'][0]);

        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'filterKeyWords', [[
            'keywordType' => 'title',
            'keyword' => 'keyword',
        ]]);
        $this->assertEquals('keyword', $result['filenameLike']);
    }

    public function testFilterTag()
    {
        $params = [
            [
                'functionName' => 'findByTagId',
                'runTimes' => 1,
                'returnValue' => [['fileId' => 1]],
            ],
        ];
        $this->mockBiz('File:UploadFileTagDao', $params);

        $result = ReflectionUtils::invokeMethod($this->getUploadFileService(), 'filterTag', [[
            'tagId' => 1,
        ]]);
        $this->assertEquals(1, $result['ids'][0]);
    }

    protected function createUser($user)
    {
        $userInfo = [];
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
