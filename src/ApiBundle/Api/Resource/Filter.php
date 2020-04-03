<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Util\CdnUrl;

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

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function filter(&$data)
    {
        if (!$data || !is_array($data)) {
            return null;
        }

        $this->defaultTimeFilter($data);

        $filteredData = array();
        foreach (array(self::SIMPLE_MODE, self::PUBLIC_MODE, self::AUTHENTICATED_MODE) as $mode) {
            $property = $mode.'Fields';
            if (property_exists($this, $property) && $this->{$property}) {
                $partData = ArrayToolkit::parts($data, $this->$property);
                if (method_exists($this, $property)) {
                    $this->$property($partData);
                }

                $filteredData += $partData;
                if ($this->mode == str_replace('Fields', '', $property)) {
                    break;
                }
            }
        }

        if ($filteredData) {
            $data = $filteredData;
        }
    }

    public function filters(&$dataSet)
    {
        if (!$dataSet || !is_array($dataSet)) {
            return;
        }

        if (array_key_exists('data', $dataSet) && array_key_exists('paging', $dataSet)) {
            foreach ($dataSet['data'] as &$data) {
                $this->filter($data);
            }
        } else {
            foreach ($dataSet as &$data) {
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
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function ($matches) {
            $cdn = new CdnUrl();
            $cdnUrl = $cdn->get('content');
            if (!empty($cdnUrl)) {
                $absoluteUrl = AssetHelper::getScheme().':'.rtrim($cdnUrl, '/').'/'.ltrim($matches[1], '/');
            } else {
                $absoluteUrl = AssetHelper::uriForPath('/'.ltrim($matches[1], '/'));
            }

            return "src=\"{$absoluteUrl}\"";
        }, $html);

        return $html;
    }

    protected function convertFilePath($filePath)
    {
        $cdn = new CdnUrl();
        $cdnUrl = $cdn->get('content');
        if (!empty($cdnUrl)) {
            $url = AssetHelper::getScheme().':'.rtrim($cdnUrl, '/').'/'.ltrim($filePath, '/');
        } else {
            $url = AssetHelper::uriForPath('/'.ltrim($filePath, '/'));
        }

        return $url;
    }
}
