<?php

namespace Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;
use Biz\User\UserFieldException;

class UserFieldServiceImpl extends BaseService implements UserFieldService
{
    public function getField($id)
    {
        return $this->getUserFieldDao()->get($id);
    }

    public function addUserField($fields)
    {
        if (empty($fields['field_title'])) {
            $this->createNewException(UserFieldException::TITLE_REQUIRED());
        }

        if (empty($fields['field_seq'])) {
            $this->createNewException(UserFieldException::SEQ_REQUIRED());
        }

        if (!intval($fields['field_seq'])) {
            $this->createNewException(UserFieldException::SEQ_INVALID());
        }

        $fieldName = $this->checkType($fields['field_type']);
        if (false == $fieldName) {
            $this->createNewException(UserFieldException::TYPE_INVALID());
        }

        $field['fieldName'] = $fieldName;
        $field['title'] = $fields['field_title'];
        $field['seq'] = $fields['field_seq'];
        $field['enabled'] = 0;
        $field['detail'] = strstr($fieldName, 'selectField') && !empty($fields['detail']) ? $fields['detail'] : '';
        if (isset($fields['field_enabled'])) {
            $field['enabled'] = 1;
        }
        $field['createdTime'] = time();

        return $this->getUserFieldDao()->create($field);
    }

    public function countFields($condition)
    {
        return $this->getUserFieldDao()->count($condition);
    }

    public function getFieldsOrderBySeq()
    {
        return $this->getUserFieldDao()->getFieldsOrderBySeq();
    }

    public function getEnabledFieldsOrderBySeq()
    {
        $fields = $this->getUserFieldDao()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($fields); ++$i) {
            if (strstr($fields[$i]['fieldName'], 'textField')) {
                $fields[$i]['type'] = 'text';
            }
            if (strstr($fields[$i]['fieldName'], 'varcharField')) {
                $fields[$i]['type'] = 'varchar';
            }
            if (strstr($fields[$i]['fieldName'], 'intField')) {
                $fields[$i]['type'] = 'int';
            }
            if (strstr($fields[$i]['fieldName'], 'floatField')) {
                $fields[$i]['type'] = 'float';
            }
            if (strstr($fields[$i]['fieldName'], 'dateField')) {
                $fields[$i]['type'] = 'date';
            }
            if (strstr($fields[$i]['fieldName'], 'selectField')) {
                $fields[$i]['type'] = 'select';
            }
        }

        return $fields;
    }

    public function updateField($id, $fields)
    {
        $fields = ArrayToolkit::filter($fields, [
            'title' => '',
            'seq' => '',
            'enabled' => 0,
            'detail' => [],
        ]);

        if (isset($fields['title']) && empty($fields['title'])) {
            $this->createNewException(UserFieldException::TITLE_REQUIRED());
        }

        if (isset($fields['seq']) && empty($fields['seq'])) {
            $this->createNewException(UserFieldException::SEQ_REQUIRED());
        }

        if (isset($fields['seq']) && !intval($fields['seq'])) {
            $this->createNewException(UserFieldException::SEQ_INVALID());
        }
        $field = $this->getField($id);
        $fields['detail'] = strstr($field['fieldName'], 'selectField') && !empty($fields['detail']) ? $fields['detail'] : '';

        return $this->getUserFieldDao()->update($id, $fields);
    }

    public function dropField($id)
    {
        $field = $this->getUserFieldDao()->get($id);

        $this->getUserService()->dropFieldData($field['fieldName']);

        $this->getUserFieldDao()->delete($id);
    }

    protected function checkType($type)
    {
        $fieldName = '';
        if ('text' == $type) {
            for ($i = 1; $i < 11; ++$i) {
                $field = $this->getUserFieldDao()->getByFieldName('textField'.$i);
                if (!$field) {
                    $fieldName = 'textField'.$i;
                    break;
                }
            }
        }
        if ('int' == $type) {
            for ($i = 1; $i < 6; ++$i) {
                $field = $this->getUserFieldDao()->getByFieldName('intField'.$i);
                if (!$field) {
                    $fieldName = 'intField'.$i;
                    break;
                }
            }
        }
        if ('date' == $type) {
            for ($i = 1; $i < 6; ++$i) {
                $field = $this->getUserFieldDao()->getByFieldName('dateField'.$i);
                if (!$field) {
                    $fieldName = 'dateField'.$i;
                    break;
                }
            }
        }
        if ('float' == $type) {
            for ($i = 1; $i < 6; ++$i) {
                $field = $this->getUserFieldDao()->getByFieldName('floatField'.$i);
                if (!$field) {
                    $fieldName = 'floatField'.$i;
                    break;
                }
            }
        }
        if ('select' == $type) {
            for ($i = 1; $i < 6; ++$i) {
                $field = $this->getUserFieldDao()->getByFieldName('selectField'.$i);
                if (!$field) {
                    $fieldName = 'selectField'.$i;
                    break;
                }
            }
        }
        if ('varchar' == $type) {
            for ($i = 1; $i < 11; ++$i) {
                $field = $this->getUserFieldDao()->getByFieldName('varcharField'.$i);
                if (!$field) {
                    $fieldName = 'varcharField'.$i;
                    break;
                }
            }
        }
        if ('' == $fieldName) {
            return false;
        }

        return $fieldName;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getUserFieldDao()
    {
        return $this->createDao('User:UserFieldDao');
    }
}
