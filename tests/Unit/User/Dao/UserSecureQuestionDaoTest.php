<?php

namespace Tests\Unit\User\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UserSecureQuestionDaoTest extends BaseDaoTestCase
{
    public function testFindByUserId()
    {
        $mockData = $this->getDefaultMockFields();

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $results = $this->getDao()->findByUserId($mockData['userId']);

        $this->assertEquals(2, count($results));
        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $this->getCompareKeys());
        }
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'securityQuestionCode' => 'teacher',
            'securityAnswer' => '123456',
            'securityAnswerSalt' => 'abcde',
        );
    }
}
