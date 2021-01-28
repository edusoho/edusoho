<?php

namespace Tests\Dao\Annotation;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Tests\Example\Dao\Impl\AnnotationExampleDaoImpl;
use Tests\IntegrationTestCase;

class MetadataReaderTest extends IntegrationTestCase
{
    public function testRead_SaveCache()
    {
        $reader = new MetadataReader('/tmp/metadatareadertest');

        $dao = new AnnotationExampleDaoImpl($this->createBiz());
        $metadata = $reader->read($dao);

        $this->assertEquals('Row', $metadata['strategy']);

        $filename = str_replace('\\', '_', is_string($dao) ? $dao : get_class($dao)).'.php';

        $this->assertFileExists("/tmp/metadatareadertest/{$filename}");
    }

    public function testRead_ReadFromCache()
    {
        $reader = new MetadataReader('/tmp/metadatareadertest');

        $dao = new AnnotationExampleDaoImpl($this->createBiz());
        $metadata = $reader->read($dao);

        $this->assertArrayHasKey('cached_time', $metadata);
    }
}
