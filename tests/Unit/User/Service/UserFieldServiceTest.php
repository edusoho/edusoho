<?php

namespace Tests\Unit\User\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class UserFieldServiceTest extends BaseTestCase
{
    public function testGetField()
    {
        $this->mockBiz(
            'User:UserFieldDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 2, 'fieldName' => 'test'),
                    'withParams' => array(2),
                ),
            )
        );
        $result = $this->getUserFieldService()->getField(2);
        $this->assertEquals(array('id' => 2, 'fieldName' => 'test'), $result);
    }

    public function testAddUserField()
    {
        $field = array(
            'field_type' => 'text',
            'field_title' => 'ceshi',
            'field_seq' => 1,
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

        $this->assertEquals(1, $returnField['id']);
        $this->assertEquals('textField1', $returnField['fieldName']);
        $this->assertEquals($field['field_title'], $returnField['title']);
        $this->assertEquals($field['field_seq'], $returnField['seq']);
        $this->assertEquals($field['field_enabled'], $returnField['enabled']);
    }

    /**
     * @expectedException \Biz\User\UserFieldException
     */
    public function testAddUserFieldWithErrorType()
    {
        $field = array(
            'field_type' => 'textaaaaaaaaa',
            'field_title' => 'ceshi',
            'field_seq' => 1,
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
    }

    /**
     * @expectedException \Biz\User\UserFieldException
     */
    public function testAddUserFieldWithEmptyTitle()
    {
        $field = array(
            'field_type' => 'textaaaaaaaaa',
            'field_title' => '',
            'field_seq' => 1,
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
    }

    /**
     * @expectedException \Biz\User\UserFieldException
     */
    public function testAddUserFieldWithErrorSeq()
    {
        $field = array(
            'field_type' => 'textaaaaaaaaa',
            'field_title' => '',
            'field_seq' => 'aas',
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
    }

    public function testSearchFieldCount()
    {
        $field = array(
            'field_type' => 'text',
            'field_title' => 'ceshi',
            'field_seq' => 1,
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
        $returnField = $this->getUserFieldService()->addUserField($field);

        $count = $this->getUserFieldService()->countFields(array('fieldName' => 'textField', 'enabled' => 1));

        $this->assertEquals(2, $count);
    }

    public function testGetFieldsOrderBySeq()
    {
        $this->mockBiz(
            'User:UserFieldDao',
            array(
                array(
                    'functionName' => 'getFieldsOrderBySeq',
                    'returnValue' => array('id' => 2, 'fieldName' => 'test'),
                ),
            )
        );
        $result = $this->getUserFieldService()->getFieldsOrderBySeq();
        $this->assertEquals(array('id' => 2, 'fieldName' => 'test'), $result);
    }

    public function testGetAllFieldsOrderBySeqAndEnabled()
    {
        $field = array(
            'field_type' => 'text',
            'field_title' => 'ceshi',
            'field_seq' => 1,
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
        $returnField = $this->getUserFieldService()->addUserField($field);

        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        $this->assertEquals(true, is_array($fields));
    }

    public function testGetEnabledFieldsOrderBySeq()
    {
        $fields = array(
            array('id' => 1, 'fieldName' => 'varcharField1', 'type' => ''),
            array('id' => 1, 'fieldName' => 'intField', 'type' => ''),
            array('id' => 1, 'fieldName' => 'floatField', 'type' => ''),
            array('id' => 1, 'fieldName' => 'dateField', 'type' => ''),
        );
        $this->mockBiz(
            'User:UserFieldDao',
            array(
                array(
                    'functionName' => 'getEnabledFieldsOrderBySeq',
                    'returnValue' => $fields,
                    'withParams' => array(),
                ),
            )
        );
        $result = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $this->assertEquals('varchar', $result[0]['type']);
        $this->assertEquals('int', $result[1]['type']);
        $this->assertEquals('float', $result[2]['type']);
        $this->assertEquals('date', $result[3]['type']);
    }

    /**
     * @group current
     */
    public function testUpdateField()
    {
        $field = array(
            'field_type' => 'text',
            'field_title' => 'ceshi',
            'field_seq' => 1,
            'field_enabled' => 1,
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

        $field1 = $this->getUserFieldService()->updateField($returnField['id'], array('title' => 'bbbbb', 'seq' => 1));

        $this->assertEquals('bbbbb', $field1['title']);
    }

    public function testDropField()
    {
        $this->mockBiz(
            'User:UserFieldDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 2, 'fieldName' => 'test'),
                    'withParams' => array(2),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(2),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'dropFieldData',
                    'withParams' => array('test'),
                ),
            )
        );
        $result = $this->getUserFieldService()->dropField(2);
        $this->getUserService()->shouldHaveReceived('dropFieldData');
        $this->getUserFieldDao()->shouldHaveReceived('delete');
        $this->assertNull($result);
    }

    public function testCheckType()
    {
        $service = $this->getUserFieldService();
        $this->mockBiz(
            'User:UserFieldDao',
            array(
                array(
                    'functionName' => 'getByFieldName',
                    'returnValue' => array(),
                    'withParams' => array('intField1'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByFieldName',
                    'returnValue' => array(),
                    'withParams' => array('dateField1'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByFieldName',
                    'returnValue' => array(),
                    'withParams' => array('floatField1'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByFieldName',
                    'returnValue' => array(),
                    'withParams' => array('varcharField1'),
                    'runTimes' => 1,
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($service, 'checkType', array('int'));
        $this->assertEquals('intField1', $result);

        $result = ReflectionUtils::invokeMethod($service, 'checkType', array('date'));
        $this->assertEquals('dateField1', $result);

        $result = ReflectionUtils::invokeMethod($service, 'checkType', array('float'));
        $this->assertEquals('floatField1', $result);

        $result = ReflectionUtils::invokeMethod($service, 'checkType', array('varchar'));
        $this->assertEquals('varcharField1', $result);

        $result = ReflectionUtils::invokeMethod($service, 'checkType', array('test'));
        $this->assertFalse($result);
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getUserFieldDao()
    {
        return $this->createDao('User:UserFieldDao');
    }
}
