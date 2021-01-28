<?php

namespace Tests\Unit\Activity\Type;

class DownloadTest extends BaseTypeTestCase
{
    const TYPE = 'download';

    public function testCreate()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'create',
                    'returnValue' => [
                        'id' => 1,
                    ],
                ],
            ]
        );

        $fields = [
            'materials' => json_encode([
                1 => ['id' => 1],
            ]),
        ];

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->create($fields);

        $this->assertEquals(1, $downloadActivity['id']);
    }

    public function testCopy()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'create',
                    'returnValue' => [
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => [1, 2],
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [1],
                    'returnValue' => [
                        'id' => 1,
                        'mediaCount' => 2,
                        'fileIds' => [1, 2],
                    ],
                ],
            ]
        );
        $activity = ['mediaId' => 1];

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->copy($activity);

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testSync()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'update',
                    'returnValue' => [
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => [1, 2],
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [1],
                    'returnValue' => [
                        'id' => 1,
                        'mediaCount' => 2,
                        'fileIds' => [1, 2],
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [2],
                    'returnValue' => [
                        'id' => 2,
                        'mediaCount' => 1,
                        'fileIds' => [1],
                    ],
                ],
            ]
        );

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->sync(['mediaId' => 1], ['mediaId' => 2]);

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testUpdate()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'update',
                    'returnValue' => [
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => [1, 2],
                    ],
                ],
            ]
        );

        $fields = [
            'materials' => json_encode([
                1 => ['id' => 1],
            ]),
        ];

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->update(2, $fields, []);

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testDelete()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'delete',
                    'withParams' => [2],
                    'returnValue' => true,
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [2],
                    'returnValue' => [
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => [1, 2],
                    ],
                ],
            ]
        );

        $type = $this->getActivityConfig(self::TYPE);
        $result = $type->delete(2);

        $this->assertEquals(true, $result);
    }

    public function testGet()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'get',
                    'withParams' => [2],
                    'returnValue' => [
                        'id' => 2,
                        'mediaCount' => 1,
                        'fileIds' => [1],
                    ],
                ],
            ]
        );

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->get(2);

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testFind()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            [
                [
                    'functionName' => 'findByIds',
                    'withParams' => [[2]],
                    'returnValue' => [
                        0 => [
                            'id' => 2,
                            'mediaCount' => 1,
                            'fileIds' => [1],
                        ],
                    ],
                ],
            ]
        );

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivities = $type->find([2]);
        $downloadActivity = reset($downloadActivities);

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testMaterialSupported()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $result = $type->materialSupported();

        $this->assertEquals(true, $result);
    }
}
