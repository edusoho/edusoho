<?php

namespace AppBundle\Component\Office;

use AppBundle\Common\FileToolkit;

abstract class BaseHelper implements OfficeHelperInterface
{
    public function read($filePath)
    {
        $result = array();
        $data = unserialize(file_get_contents($filePath));
        foreach ($data as $item) {
            $result[] = unserialize(file_get_contents($item));
        }

        return $result;
    }

    public function delete($filePath)
    {
        $data = unserialize(file_get_contents($filePath));
        foreach ($data as $item) {
            FileToolkit::remove($item);
        }

        FileToolkit::remove($filePath);
    }

    abstract public function write($fileName, $filePath);
}
