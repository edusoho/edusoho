<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\Util\RequestUtil;
use AppBundle\Common\ArrayToolkit;

abstract class Filter
{
    /**
     * 简化模式,只返回少量的非隐私字段
     */
    const SIMPLE_MODE = 'simple';

    /**
     * 公开模式,返回未登录用户可访问的字段
     */
    const PUBLIC_MODE = 'public';

    /**
     * 认证模式,返回用户登录后可访问的字段
     */
    const AUTHENTICATED_MODE = 'authenticated';

    protected $mode = self::PUBLIC_MODE;

    protected $fieldProperties;

    public function __construct()
    {
        $this->getFieldProperties();
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function filter(&$data)
    {
        if (empty($data)) {
            return null;
        }

        $this->defaultTimeFilter($data);

        if ($this->fieldProperties) {
            $filteredData = array();
            foreach ($this->fieldProperties as $property) {
                $partData = ArrayToolkit::parts($data, $this->$property);
                if (method_exists($this, $property)) {
                    $this->$property($partData);
                }

                $filteredData += $partData;
                if ($this->mode == str_replace('Fields', '', $property)) {
                    break;
                }
            }

            $data = $filteredData;
        }
    }

    private function isFieldsProperty(\ReflectionProperty $property)
    {
        return strpos($property->getName(), 'Fields') > 0;
    }

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

    private function defaultTimeFilter(&$data)
    {
        if (isset($data['createdTime']) && is_numeric($data['createdTime'])) {
            $data['createdTime'] = date('c', $data['createdTime']);
        }

        if (isset($data['updatedTime']) && is_numeric($data['updatedTime'])) {
            $data['updatedTime'] = date('c', $data['updatedTime']);
        }
    }

    protected function convertAbsoluteUrl($html)
    {
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function($matches) {
            $absoluteUrl = RequestUtil::asset($matches[1]);
            return "src=\"{$absoluteUrl}\"";
        }, $html);

        return $html;

    }

    private function getFieldProperties()
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $reflectionProperties = $reflectionClass->getProperties();
        $actualFieldsProperties = array();
        foreach ($reflectionProperties as $key => $property) {
            if ($this->isFieldsProperty($property)) {
                $actualFieldsProperties[] = $property->getName();
            }
        }

        $this->fieldProperties = $actualFieldsProperties;
    }
}