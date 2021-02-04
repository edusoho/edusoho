<?php

namespace OpenLivePlugin\Common\Office;

interface OfficeHelperInterface
{
    public function write($fileName, $filePath);

    public function read($filePath);

    public function delete($filePath);
}
