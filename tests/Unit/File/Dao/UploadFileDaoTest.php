<?php

namespace Tests\Unit\File\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UploadFileDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('id' => 1));
        $expected[] = $this->mockDataObject(array('etag' => 'test', 'targetType' => 'test', 'id' => 2, 'convertHash' => 'hash2', 'hashId' => 'id2', 'usedCount' => 10, 'createdUserId' => 5));
        $expected[] = $this->mockDataObject(array('globalId' => 'testId', 'convertStatus' => 'none', 'id' => 3, 'convertHash' => 'hash3', 'hashId' => 'id3', 'usedCount' => 3));
        $expected[] = $this->mockDataObject(array('targetId' => 2, 'isPublic' => 0, 'id' => 4, 'convertHash' => 'hash4', 'hashId' => 'id4', 'createdTime' => 2000, 'audioConvertStatus' => 'doing'));
        $expected[] = $this->mockDataObject(array('type' => 'video', 'storage' => 'cloud', 'filename' => 'test', 'id' => 5, 'convertHash' => 'hash5', 'hashId' => 'id5', 'createdTime' => 200000));
        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ),
            array(
                'condition' => array('etag' => 'test'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('targetType' => 'test'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('targetTypes' => array('test')),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('useTypes' => array('type')),
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ),
            array(
                'condition' => array('useType' => 'y'),
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ),
            array(
                'condition' => array('globalId' => 'testId'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('globalIds' => array('testId')),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('existGlobalId' => 'id'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('noTargetType' => 'type'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('noTargetTypes' => array('type')),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('convertStatus' => 'none'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('targetId' => 2),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('status' => 'ok'),
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ),
            array(
                'condition' => array('isPublic' => 0),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('targets' => array(2)),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('type' => 'video'),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('types' => array('video')),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('storage' => 'cloud'),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('filename' => 'es'),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('ids' => array(1)),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('startDate' => 150000),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('endDate' => 10000),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('startCount' => 8),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('endCount' => 4),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('createdUserIds' => array(5)),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('createdUserId' => 5),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('idsOr' => array(1)),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('audioConvertStatus' => 'doing'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('inAudioConvertStatus' => array('doing')),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testCreate()
    {
        $result = $this->getDao()->create($this->getDefaultMockFields());
        $this->assertEquals(array(), $result);

        $fields = array_merge($this->getDefaultMockFields(), array('id' => 1));
        $result = $this->getDao()->create($fields);
        $this->assertArrayEquals($fields, $result, $this->getCompareKeys());
    }

    public function testGetByHashId()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->getByHashId('id1');
        $this->assertArrayEquals($expected, $result, $this->getCompareKeys());
    }

    public function testGetByGlobalId()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->getByGlobalId('id');
        $this->assertArrayEquals($expected, $result, $this->getCompareKeys());
    }

    public function testGetByConvertHash()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->getByConvertHash('hash1');
        $this->assertArrayEquals($expected, $result, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->findByIds(array(1));
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    public function testFindByTargetTypeAndTargetIds()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->findByTargetTypeAndTargetIds('type', array());
        $this->assertEquals(array(), $result);

        $result = $this->getDao()->findByTargetTypeAndTargetIds('type', array(3));
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    public function testFindCloudFilesByIds()
    {
        $expected = $this->mockDataObject(array('id' => 1, 'storage' => 'cloud'));
        $result = $this->getDao()->findCloudFilesByIds(array());
        $this->assertEquals(array(), $result);

        $result = $this->getDao()->findCloudFilesByIds(array(1));
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    public function testCountByEtag()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->countByEtag('');
        $this->assertEquals(0, $result);

        $result = $this->getDao()->countByEtag('etag');
        $this->assertEquals(1, $result);
    }

    public function testDeleteByGlobalId()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $this->getDao()->deleteByGlobalId('id');
        $result = $this->getDao()->getByGlobalId('id');
        $this->assertNull($result);
    }

    public function testWaveUsedCount()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $this->getDao()->waveUsedCount(1, 1);
        $result = $this->getDao()->get(1);
        $this->assertEquals(6, $result['usedCount']);
    }

    public function testGetByTargetType()
    {
        $expected = $this->mockDataObject(array('id' => 1));
        $result = $this->getDao()->getByTargetType('type');
        $this->assertArrayEquals($expected, $result, $this->getCompareKeys());
    }

    public function testFindHeadLeaderFiles()
    {
        $expected = $this->mockDataObject(array('id' => 1, 'targetType' => 'headLeader'));
        $result = $this->getDao()->findHeadLeaderFiles();
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'globalId' => 'id',
            'status' => 'ok',
            'hashId' => 'id1',
            'targetId' => 3,
            'targetType' => 'type',
            'useType' => 'type',
            'filename' => 'name',
            'etag' => 'etag',
            'length' => 1000,
            'convertHash' => 'hash1',
            'convertStatus' => 'waiting',
            'type' => 'document',
            'storage' => 'local',
            'isPublic' => 1,
            'usedCount' => 5,
            'createdUserId' => 2,
            'createdTime' => 100000,
            'audioConvertStatus' => 'waiting',
        );
    }
}
