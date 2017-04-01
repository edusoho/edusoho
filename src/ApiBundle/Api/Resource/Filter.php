<?php

namespace ApiBundle\Api\Resource;

abstract class Filter
{
    protected $publicFields;

    protected $fieldMode = 'public';

    public function setFieldMode($fieldMode)
    {
        $this->fieldMode = $fieldMode;
    }

    public function filter(&$data)
    {
        if (empty($data)) {
            return null;
        }

        $this->defaultFieldsFilter($data);

        $this->defaultTimeFilter($data);

        static::customFilter($data);
    }

    abstract protected function customFilter(&$data);

    public function filters(&$dataSet)
    {
        if (!$dataSet) {
            return;
        }

        if (array_key_exists('data', $dataSet) && array_key_exists('paging', $dataSet)) {
            foreach ($dataSet['data'] as &$data) {
                $this->filter($data);
            }
        } else {
            foreach($dataSet as &$data) {
                $this->filter($data);
            }
        }
    }

    private function defaultFieldsFilter(&$data)
    {
        $filterFields = $this->fieldMode.'Fields';
        if ($this->$filterFields) {
            foreach (array_keys($data) as $field) {
                if (!in_array($field, $this->$filterFields)) {
                    unset($data[$field]);
                }
            }
        }
    }

    private function defaultTimeFilter(&$data)
    {
        if (isset($data['createdTime']) && is_numeric($data['createdTime'])) {
            $data['createdTime'] = date('c', $data['createdTime']);
        }

        if (isset($data['updatedTime']) && is_numeric($data['updatedTime'])) {
            $data['updatedTime'] = date('c', $data['updatedTime']);
        }
    }
}