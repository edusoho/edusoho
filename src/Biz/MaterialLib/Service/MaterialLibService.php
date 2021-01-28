<?php

namespace Biz\MaterialLib\Service;

interface MaterialLibService
{
    public function get($id);

    public function getByGlobalId($globalId);

    public function edit($fileId, $fields);

    public function delete($id);

    public function batchDelete($ids);

    public function batchTagEdit($fileIds, $tagNames);

    public function batchShare($ids);

    public function unShare($id);

    public function download($id);

    public function reconvert($globalId, $options);

    public function getDefaultHumbnails($globalId);

    public function getThumbnail($globalId, $options);

    public function player($globalId, $ssl = false);
}
