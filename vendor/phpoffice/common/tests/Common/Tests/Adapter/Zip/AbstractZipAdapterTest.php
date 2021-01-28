<?php

namespace Common\Tests\Adapter\Zip;

use PhpOffice\Common\Tests\TestHelperZip;

abstract class AbstractZipAdapterTest extends \PHPUnit\Framework\TestCase
{
    protected $zipTest;

    /**
     * Returns a new instance of the adapter to test
     * @return \PhpOffice\Common\Adapter\Zip\ZipInterface
     */
    abstract protected function createAdapter();

    public function setUp()
    {
        parent::setUp();

        $pathResources = PHPOFFICE_COMMON_TESTS_BASE_DIR.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR;
        $this->zipTest = tempnam(sys_get_temp_dir(), 'PhpOfficeCommon');
        copy($pathResources.'Sample_01_Simple.pptx', $this->zipTest);
    }

    public function tearDown()
    {
        parent::tearDown();

        if (is_file($this->zipTest)) {
            unlink($this->zipTest);
        }
    }

    public function testOpen()
    {
        $adapter = $this->createAdapter();
        $this->assertSame($adapter, $adapter->open($this->zipTest));
    }

    public function testClose()
    {
        $adapter = $this->createAdapter();
        $adapter->open($this->zipTest);
        $this->assertSame($adapter, $adapter->close());
    }

    public function testAddFromString()
    {
        $expectedPath = 'file.test';
        $expectedContent = 'Content';

        $adapter = $this->createAdapter();
        $adapter->open($this->zipTest);
        $this->assertSame($adapter, $adapter->addFromString($expectedPath, $expectedContent));
        $adapter->close();

        $this->assertTrue(TestHelperZip::assertFileExists($this->zipTest, $expectedPath));
        $this->assertTrue(TestHelperZip::assertFileContent($this->zipTest, $expectedPath, $expectedContent));
    }
}
