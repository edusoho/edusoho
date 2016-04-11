<?php

namespace MaterialLib\Service\MaterialLib;

interface MaterialLibService
{
    public function get($id);

    public function edit($globalId, $fields);

    public function delete($id);

    public function batchDelete($ids);

    public function batchShare($ids);

    public function download($id);

    public function reconvert($globalId, $options);

    public function getDefaultHumbnails($globalId);

    public function getThumbnail($globalId, $options);

    public function player($globalId);

    public function synData();

}
