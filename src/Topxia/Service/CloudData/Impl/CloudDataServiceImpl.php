<?php

namespace Topxia\Service\CloudData\Impl;

use Topxia\Service\CloudData\CloudDataService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudDataServiceImpl implements CloudDataService
{
    public function push($name, array $body = array(), $timestamp, $tryTimes = 0)
    {
        try {
            return CloudAPIFactory::create('event')->push($name, $body, $timestamp);
        } catch (\Exception $e) {
            if ($tryTimes == 0) {
                $tryTimes++;
                $this->push($name, $body, $timestamp, $tryTimes);
            } else {
                $fields = array(
                    'name'      => $name,
                    'body'      => $body,
                    'timestamp' => $timestamp
                );
                $this->getCloudDataDao()->add($fields);
            }
        }
    }
}
