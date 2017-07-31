<?php

namespace AppBundle\Component\Office;

interface OfficeHelpInterface
{
    public function export($fileName, $filePath);

    public function handleContent($filePath);
}
