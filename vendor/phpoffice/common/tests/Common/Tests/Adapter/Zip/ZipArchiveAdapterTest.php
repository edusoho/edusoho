<?php

namespace Common\Tests\Adapter\Zip;

use PhpOffice\Common\Adapter\Zip\ZipArchiveAdapter;
use PhpOffice\Common\Tests\TestHelperZip;

class ZipArchiveAdapterTest extends AbstractZipAdapterTest
{
    protected function createAdapter()
    {
        return new ZipArchiveAdapter();
    }
}
