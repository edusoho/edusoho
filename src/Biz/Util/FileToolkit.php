<?php

namespace Biz\Util;

class FileToolkit
{
    public function file_exists($filename)
    {
        return file_exists($filename);
    }
}
