<?php

namespace MaterialLib\Service\MaterialLib;

interface CloudFileService
{
    public function search($conditions);

    public function get($globalId);

    public function edit($globalId, $fields);

    public function delete($globalId);

    public function download($globalId);
}
