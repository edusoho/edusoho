<?php

namespace Biz\Content\Type;

abstract class ContentType
{
    abstract public function getAlias();

    abstract public function getName();

    public function getBasicFields()
    {
        return array('title', 'content');
    }

    public function getExtendedFields()
    {
        return array();
    }

    public function getFields()
    {
        $settingFields = array('publishedTime', 'promoted', 'sticky', 'featured');

        return array_merge($this->getBasicFields(), $this->getExtendedFields(), $settingFields);
    }

    public function convert($fields)
    {
        return $this->_convert($fields, 'in');
    }

    public function reconvert($fields)
    {
        return $this->_convert($fields, 'out');
    }

    protected function _convert($fields, $mode = 'in')
    {
        $extendFields = $this->getExtendedFields();
        if (empty($extendFields) || !is_array($extendFields)) {
            return $fields;
        }

        foreach ($extendFields as $internalField => $externalField) {
            if ($mode == 'in') {
                $toField = $internalField;
                $fromField = $externalField;
            } else {
                $toField = $externalField;
                $fromField = $internalField;
            }

            if (isset($fields[$fromField])) {
                $fields[$toField] = $fields[$fromField];
                unset($fields[$fromField]);
            }
        }

        return $fields;
    }
}
