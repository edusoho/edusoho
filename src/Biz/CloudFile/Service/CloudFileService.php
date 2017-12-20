<?php

namespace Biz\CloudFile\Service;

interface CloudFileService
{
    public function search($conditions, $start, $limit);

    public function getByGlobalId($globalId);

    public function player($globalId, $ssl = false);

    public function edit($globalId, $fields);

    public function delete($globalId);

    public function batchDelete($globalIds);

    public function download($globalId);

    public function reconvert($globalId, $options);

    public function getDefaultHumbnails($globalId);

    public function getThumbnail($globalId, $options);

    public function getStatistics($options = array());

    public function deleteCloudMP4Files($userId, $callback);

    public function hasMp4Video();
}
