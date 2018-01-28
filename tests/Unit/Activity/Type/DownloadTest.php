<?php

namespace Tests\Unit\Activity\Type;

class DownloadTest extends BaseTypeTestCase
{
    const TYPE = 'download';

    public function testCreate()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array(
                        'id' => 1,
                    )
                )
            )
        );

        $fields = array(
            'materials' => json_encode(array(
                1 => array('id' => 1),
            )),
        );

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->create($fields);

        $this->assertEquals(1, $downloadActivity['id']);
    }

    public function testCopy()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array(
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => array(1,2)
                    )
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'mediaCount' => 2,
                        'fileIds' => array(1,2)
                    )
                ),
            )
        );
        $activity = array('mediaId' => 1);

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->copy($activity);

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testSync()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => array(
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => array(1,2)
                    )
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'mediaCount' => 2,
                        'fileIds' => array(1,2)
                    )
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(2),
                    'returnValue' => array(
                        'id' => 2,
                        'mediaCount' => 1,
                        'fileIds' => array(1)
                    )
                ),
            )
        );

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->sync(array('mediaId' => 1), array('mediaId' => 2));

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testUpdate()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => array(
                        'id' => 2,
                        'mediaCount' => 2,
                        'fileIds' => array(1,2)
                    )
                ),
            )
        );

        $fields = array(
            'materials' => json_encode(array(
                1 => array('id' => 1),
            )),
        );

        $type = $this->getActivityConfig(self::TYPE);
        $downloadActivity = $type->update(2, $fields, array());

        $this->assertEquals(2, $downloadActivity['id']);
    }

    public function testDelete()
    {
        $this->mockBiz('Activity:DownloadActivityDao',
            array(
                array(
                    'functionName' => 'delete',
                    'withParams' => array(2),
                    'returnValue' => true
                ),
            )
        );

        $type = $this->getActivityConfig(self::TYPE);
        $result = $type->delete(2);

        $this->assertEquals(true, $result);
    }


}