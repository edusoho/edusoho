<?php

namespace Common\Tests\Adapter\Zip;

use PhpOffice\Common\Adapter\Zip\PclZipAdapter;
use PhpOffice\Common\Tests\TestHelperZip;

class PclZipAdapterTest extends AbstractZipAdapterTest
{
    protected function createAdapter()
    {
        return new PclZipAdapter();
    }
}
