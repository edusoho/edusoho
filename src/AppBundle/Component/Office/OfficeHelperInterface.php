<?php

namespace AppBundle\Component\Office;

interface OfficeHelperInterface
{
    public function write($fileName, $filePath);

    public function read($fileName, $filePath);

    public function delete($filePath);
}
