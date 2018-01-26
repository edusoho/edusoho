<?php

namespace Codeages\Biz\Framework\Dao\Annotation;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Finder\Finder;

class AnnotationCacheBuilder
{
    protected $loader;

    public function __construct(ClassLoader $loader)
    {
        $this->loader = $loader;
    }

    public function build(array $namespaces)
    {
        foreach ($namespaces as $namespace) {
            $this->scanNamespace($namespace);
        }
    }

    public function scanNamespace($namespace)
    {
        $cache = array();
        $reader = new AnnotationReader();
        $directories = $this->getNamespaceDirectories($namespace);
        foreach ($directories as $directory) {
            $finder = new Finder();
            $finder->in($directory);

            foreach ($finder->files()->name('*.php') as $file) {
                $class = $namespace.'\\'.str_replace(DIRECTORY_SEPARATOR, '\\', substr($file->getRelativePathname(), 0, -4));

                $classRef = new \ReflectionClass($class);
                $isDao = $classRef->implementsInterface('Codeages\Biz\Framework\Dao\DaoInterface');
                if (!$isDao) {
                    continue;
                }

                $annotation = $reader->getClassAnnotation($classRef, 'Codeages\Biz\Framework\Dao\Annotation\CacheStrategy');
                if (empty($annotation)) {
                    continue;
                }

                $cache[$class] = array(
                    'strategy' => $annotation->getName(),
                    'update_rel_fields' => array(),
                    'methods' => array(),
                );

                $methodRefs = $classRef->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methodRefs as $methodRef) {
                    $annotation = $reader->getMethodAnnotation($methodRef, 'Codeages\Biz\Framework\Dao\Annotation\RowCache');
                    if (empty($annotation)) {
                        continue;
                    }

                    $cache[$class]['update_rel_fields'] = array_merge($cache[$class]['update_rel_fields'], $annotation->getRelFields());
                    $cache[$class]['methods'][$methodRef->getName()] = array(
                        'key' => $annotation->getKey(),
                    );

                    $params = $methodRef->getParameters();
                }
            }
        }

        var_dump($cache);
    }

    public function getNamespaceDirectories($namespace)
    {
        if ('\\' !== substr($namespace, -1)) {
            $namespace .= '\\';
        }

        $directories = array();
        $prefixes = $this->loader->getPrefixesPsr4();
        foreach ($prefixes as $prefix => $prefixDirectories) {
            if (0 !== strpos($namespace, $prefix)) {
                continue;
            }
            $relativeDirectory = str_replace('\\', DIRECTORY_SEPARATOR, substr($namespace, strlen($prefix)));
            foreach ($prefixDirectories as $directory) {
                $directories[] = $directory.DIRECTORY_SEPARATOR.$relativeDirectory;
            }
        }

        return $directories;
    }
}
