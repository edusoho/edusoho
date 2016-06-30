<?php


namespace Topxia\Service\Importer;

use Topxia\Common\JoinPointToolkit;
use Topxia\Service\Common\NotFoundException;

class ImporterFactory
{
    public static function create($importerType)
    {
        $map = JoinPointToolkit::load('importer');
        if (!array_key_exists($importerType, $map)) {
            throw new NotFoundException('UNKNOWN IMPORTER TYPE: ' . $importerType);
        }

        $class = $map[$importerType];
        return new $class();
    }
}
