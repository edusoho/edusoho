<?php

namespace AppBundle\Component\Office;

interface OfficeHelperInterface
{
    public function write($fileName, $filePath);

    public function read($filePath);

    public function delete($filePath);
}
