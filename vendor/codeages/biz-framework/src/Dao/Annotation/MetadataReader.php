<?php

namespace Codeages\Biz\Framework\Dao\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Filesystem\Filesystem;

class MetadataReader
{
    private $cacheDirectory;

    public function __construct($cacheDirectory = null)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    public function read($dao)
    {
        $cache = $this->readCache($dao);
        if ($cache) {
            return $cache;
        }

        $reader = new AnnotationReader();
        $classRef = new \ReflectionClass($dao);
        $isDao = $classRef->implementsInterface('Codeages\Biz\Framework\Dao\DaoInterface');
        if (!$isDao) {
            return null;
        }

        $annotation = $reader->getClassAnnotation($classRef, 'Codeages\Biz\Framework\Dao\Annotation\CacheStrategy');
        if (empty($annotation)) {
            return null;
        }

        $metadata = array(
            'strategy' => $annotation->getName(),
            'cache_key_of_field_name' => array(),
            'cache_key_of_arg_index' => array(),
            'update_rel_query_methods' => array(),
        );

        $methodRefs = $classRef->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methodRefs as $methodRef) {
            $annotation = $reader->getMethodAnnotation($methodRef, 'Codeages\Biz\Framework\Dao\Annotation\RowCache');
            if (empty($annotation)) {
                continue;
            }

            $args = $this->getMethodArgumentNames($methodRef);
            if (empty($annotation->relFields)) {
                $annotation->relFields = $args;
            }

            $metadata['cache_key_of_field_name'][$methodRef->getName()] = $annotation->relFields;

            $args = array_flip($args);
            foreach ($annotation->relFields as $field) {
                if (empty($metadata['cache_key_of_arg_index'][$methodRef->getName()])) {
                    $metadata['cache_key_of_arg_index'][$methodRef->getName()] = array();
                }
                $metadata['cache_key_of_arg_index'][$methodRef->getName()][] = $args[$field];

                if (empty($metadata['update_rel_query_methods'][$field])) {
                    $metadata['update_rel_query_methods'][$field] = array();
                }
                $metadata['update_rel_query_methods'][$field][] = $methodRef->getName();
            }
        }

        $metadata['cache_key_of_arg_index']['get'] = array(0);
        $metadata['cache_key_of_field_name']['get'] = array('id');

        $this->saveCache($dao, $metadata);

        return $metadata;
    }

    protected function getMethodArgumentNames(\ReflectionMethod $methodRef)
    {
        $args = $methodRef->getParameters();
        $names = array();
        foreach ($args as $arg) {
            $names[] = $arg->getName();
        }

        return $names;
    }

    protected function readCache($dao)
    {
        if (!$this->cacheDirectory) {
            return null;
        }

        $filePath = $this->getCacheFilePath($this->cacheDirectory, $dao);
        if (file_exists($filePath)) {
            return include $filePath;
        }

        return null;
    }

    protected function saveCache($dao, $metadata)
    {
        if (!$this->cacheDirectory) {
            return;
        }

        $metadata['cached_time'] = time();

        $this->makeCacheDirectory($this->cacheDirectory);
        $filePath = $this->getCacheFilePath($this->cacheDirectory, $dao);
        $content = "<?php \n return ".var_export($metadata, true).';';

        file_put_contents($filePath, $content);
        $fs = new Filesystem();
        $fs->chmod($filePath, 0666);
    }

    protected function getCacheFilePath($cacheDirectory, $dao)
    {
        $filename = str_replace('\\', '_', is_string($dao) ? $dao : get_class($dao)).'.php';
        $filepath = $this->cacheDirectory.DIRECTORY_SEPARATOR.$filename;

        return $filepath;
    }

    protected function makeCacheDirectory($cacheDirectory)
    {
        $fs = new Filesystem();
        if ($fs->exists($cacheDirectory)) {
            return;
        }

        $fs->mkdir($cacheDirectory);
        $fs->chmod($cacheDirectory, 0777);
    }
}
