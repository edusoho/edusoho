<?php

namespace Tests\User;

use Biz\BaseTestCase;;

class UserFieldServiceTest extends BaseTestCase
{
    public function testAddUserField()
    {
        $field = array(
            'field_type'    => "text",
            'field_title'   => "ceshi",
            'field_seq'     => 1,
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

        $this->assertEquals(1, $returnField['id']);
        $this->assertEquals('textField1', $returnField['fieldName']);
        $this->assertEquals($field['field_title'], $returnField['title']);
        $this->assertEquals($field['field_seq'], $returnField['seq']);
        $this->assertEquals($field['field_enabled'], $returnField['enabled']);

    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddUserFieldWithErrorType()
    {
        $field = array(
            'field_type'    => "textaaaaaaaaa",
            'field_title'   => "ceshi",
            'field_seq'     => 1,
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddUserFieldWithEmptyTitle()
    {
        $field = array(
            'field_type'    => "textaaaaaaaaa",
            'field_title'   => "",
            'field_seq'     => 1,
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddUserFieldWithErrorSeq()
    {
        $field = array(
            'field_type'    => "textaaaaaaaaa",
            'field_title'   => "",
            'field_seq'     => "aas",
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

    }

    public function testSearchFieldCount()
    {
        $field = array(
            'field_type'    => "text",
            'field_title'   => "ceshi",
            'field_seq'     => 1,
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
        $returnField = $this->getUserFieldService()->addUserField($field);

        $count = $this->getUserFieldService()->countFields(array('fieldName' => 'textField', 'enabled' => 1));

        $this->assertEquals(2, $count);

    }

    public function testGetAllFieldsOrderBySeqAndEnabled()
    {
        $field = array(
            'field_type'    => "text",
            'field_title'   => "ceshi",
            'field_seq'     => 1,
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);
        $returnField = $this->getUserFieldService()->addUserField($field);

        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        $this->assertEquals(true, is_array($fields));

    }

    /**
     * @group current
     */
    public function testUpdateField()
    {
        $field = array(
            'field_type'    => "text",
            'field_title'   => "ceshi",
            'field_seq'     => 1,
            'field_enabled' => 1
        );

        $returnField = $this->getUserFieldService()->addUserField($field);

        $field1 = $this->getUserFieldService()->updateField($returnField['id'], array('title' => "bbbbb", "seq" => 1));

        $this->assertEquals("bbbbb", $field1['title']);

    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }
}
