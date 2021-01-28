<?php

namespace Tests\Unit\MaterialLib\Service;

use Biz\BaseTestCase;

class MaterialLibServiceTest extends BaseTestCase
{
    public function testGet()
    {
        $params = [
            [
                'functionName' => 'getFullFile',
                'returnValue' => ['id' => 3, 'storage' => 'cloud'],
            ],
        ];
        $this->mockBiz('File:UploadFileService', $params);
        $file = $this->getMaterialLibService()->get(10);
        $this->assertEquals($file['id'], 3);
    }

    public function testGetByGlobalId()
    {
        $biz = $this->getBiz();
        $params = [
            [
                'functionName' => 'getFileByGlobalId',
                'returnValue' => ['id' => '3', 'storage' => 'cloud'],
                'withParams' => ['098f6bcd4621d373cade4e832627b4f6'],
            ],
        ];
        $this->mockBiz('File:UploadFileService', $params);
        $file = $this->getMaterialLibService()->getByGlobalId('098f6bcd4621d373cade4e832627b4f6');
        $this->assertEquals($file['id'], 3);
    }

    public function testPlayer()
    {
        $this->mockBiz(
            'CloudFile:CloudFileService',
            [
                [
                    'functionName' => 'player',
                    'withParams' => ['098f6bcd4621d373cade4e832627b4f6', false],
                    'returnValue' => [
                        ['player' => 'video', 'token' => 'GQW4Fw47TrlCzwQj:1513216884:4pc2y0qZGuknpMmJlhhK-WMbxyQ='],
                    ],
                ],
            ]
        );
        $this->mockBiz('File:UploadFileService', [
            [
                'functionName' => 'getFileByGlobalId',
                'returnValue' => [
                    'storage' => 'cloud',
                ],
            ],
        ]);
        $player = $this->getMaterialLibService()->player('098f6bcd4621d373cade4e832627b4f6', false);
        $this->assertNotNull($player);
    }

    public function testEdit()
    {
        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'update',
                ],
            ]
        );
        $this->getMaterialLibService()->edit('3', ['audioConvertStatus' => 'success']);
        $this->getUploadFileService()->shouldHaveReceived('update');
    }

    public function testDelete()
    {
        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'deleteFile',
                    'withParams' => [3],
                    'returnValue' => [1],
                ],
            ]
        );
        $bool = $this->getMaterialLibService()->delete(3);
        $this->assertTrue($bool);
    }

    public function testBatchDelete()
    {
        $this->mockBiz(
            'MaterialLib:MaterialLibService',
            [
                [
                    'functionName' => 'batchDelete',
                    'returnValue' => ['success' => true],
                ],
            ]
        );
        $result = $this->getMaterialLibService()->batchDelete([1, 2, 3, 4]);
        $this->assertArrayEquals($result, ['success' => true]);
    }

    public function testBatchTagEdit()
    {
        $this->mockUploadFile();
        $this->mockTag();
        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'update',
                ],
            ]
        );
        $this->getMaterialLibService()->batchTagEdit([3, 4], 'tag1,tag2');
        $this->getUploadFileService()->shouldHaveReceived('update');
    }

    public function testBatchShare()
    {
        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'sharePublic',
                ],
            ]
        );
        $result = $this->getMaterialLibService()->batchShare([3, 4]);
        $this->getUploadFileService()->shouldHaveReceived('sharePublic');
        $this->assertArrayEquals($result, ['success' => true]);
    }

    public function testUnShare()
    {
        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'unsharePublic',
                ],
            ]
        );
        $result = $this->getMaterialLibService()->unShare(3);
        $this->getUploadFileService()->shouldHaveReceived('unsharePublic');
        $this->assertArrayEquals($result, ['success' => true]);
    }

    public function testDownload()
    {
        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'getDownloadMetas',
                    'returnValue' => [
                        'id' => '3',
                        'hashId' => 'course-task-14/20171212114426-njm92j6bgw0wgooo',
                        'filename' => 'test',
                    ],
                    'withParams' => [3],
                ],
            ]
        );
        $file = $this->getMaterialLibService()->download(3);
        $this->assertEquals($file['id'], '3');
    }

    public function testReconvert()
    {
        $this->mockBiz(
            'CloudFile:CloudFileService',
            [
                [
                    'functionName' => 'reconvert',
                    'withParams' => ['098f6bcd4621d373cade4e832627b4f6', []],
                ],
            ]
        );

        $params = [
            [
                'functionName' => 'getFileByGlobalId',
                'returnValue' => ['id' => '3', 'storage' => 'cloud'],
                'withParams' => ['098f6bcd4621d373cade4e832627b4f6'],
            ],
        ];
        $this->mockBiz('File:UploadFileService', $params);
        $file = $this->getMaterialLibService()->reconvert('098f6bcd4621d373cade4e832627b4f6');

        $this->assertEquals($file['id'], '3');
    }

    public function testGetDefaultHumbnails()
    {
        $this->mockBiz(
            'CloudFile:CloudFileService',
            [
                [
                    'functionName' => 'getDefaultHumbnails',
                    'withParams' => ['098f6bcd4621d373cade4e832627b4f6'],
                    'returnValue' => [
                        [
                            'no' => 'd1215e367c0d420f9ead7b8d41ee495b',
                            'url' => 'http://ese3a6b7c1d83t-pub.pub.qiqiuyun.net/4015c18c47fbd4b37a68c4e2f8bc6d26.jpg',
                        ],
                        [
                            'no' => '82d0fbb0f2334eb1a2a92607a75f7471',
                            'url' => 'http://ese3a6b7c1d83t-pub.pub.qiqiuyun.net/5b2522d9b708fcc72e62f75cfa252fd0.jpg',
                        ],
                    ],
                ],
            ]
        );
        $humbnails = $this->getMaterialLibService()->getDefaultHumbnails('098f6bcd4621d373cade4e832627b4f6');
        $this->assertArrayEquals($humbnails[0], [
            'no' => 'd1215e367c0d420f9ead7b8d41ee495b',
            'url' => 'http://ese3a6b7c1d83t-pub.pub.qiqiuyun.net/4015c18c47fbd4b37a68c4e2f8bc6d26.jpg',
        ]);
    }

    public function testGetThumbnail()
    {
        $this->mockBiz(
            'CloudFile:CloudFileService',
            [
                [
                    'functionName' => 'getThumbnail',
                    'withParams' => ['098f6bcd4621d373cade4e832627b4f6', []],
                    'returnValue' => 'http:\/\/ese3a6b7c1d83t-pub.pub.qiqiuyun.net\/attachment-14\/20170915023910-nj795r6yksg4gows\/676376d9945c3e0f_thumb',
                ],
            ]
        );

        $humbnail = $this->getMaterialLibService()->getThumbnail('098f6bcd4621d373cade4e832627b4f6');
        $this->assertEquals($humbnail, 'http:\/\/ese3a6b7c1d83t-pub.pub.qiqiuyun.net\/attachment-14\/20170915023910-nj795r6yksg4gows\/676376d9945c3e0f_thumb');
    }

    public function testGetStatistics()
    {
        $this->mockBiz(
            'CloudFile:CloudFileService',
            [
                [
                    'functionName' => 'getStatistics',
                ],
            ]
        );
        $this->getMaterialLibService()->getStatistics();
        $this->getCloudFileService()->shouldHaveReceived('getStatistics');
    }

    protected function mockUploadFile()
    {
        $file1 = [
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
        ];
        $file2 = [
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
        ];
        $this->getUploadFileDao()->create($file1);
        $this->getUploadFileDao()->create($file2);
    }

    protected function mockTag()
    {
        $tag1 = ['id' => 1, 'name' => 'tag1'];
        $tag2 = ['id' => 2, 'name' => 'tag2'];
        $this->getTagDao()->create($tag1);
        $this->getTagDao()->create($tag2);
    }

    protected function getTagDao()
    {
        return $this->createDao('Taxonomy:TagDao');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }

    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }
}
