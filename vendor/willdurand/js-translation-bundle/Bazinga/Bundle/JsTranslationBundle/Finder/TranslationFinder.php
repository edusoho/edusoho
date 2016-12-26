<?php

namespace Bazinga\Bundle\JsTranslationBundle\Finder;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

/**
 * @author William DURAND <william.durand1@gmail.com>
 * @author Markus Poerschke <markus@eluceo.de>
 */
class TranslationFinder
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param KernelInterface $kernel The kernel.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns an array of translation files for a given domain,
     * and a given locale.
     *
     * @param string $domain A domain translation name
     * @param string $locale A locale
     *
     * @return array An array of translation files.
     */
    public function get($domain, $locale)
    {
        $finder = new Finder();

        return $finder
            ->files()
            ->name($domain . '.' . $locale . '.*')
            ->followLinks()
            ->in($this->getLocations());
    }

    /**
     * Returns an array of all translation files.
     *
     * @return array An array of translation files.
     */
    public function all()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->getLocations())
            ->followLinks();

        return $finder;
    }

    /**
     * Gets translation files location.
     *
     * @return array
     */
    protected function getLocations()
    {
        $locations = array();

        if (class_exists('Symfony\Component\Validator\Validation')) {
            $r = new \ReflectionClass('Symfony\Component\Validator\Validation');

            $locations[] = dirname($r->getFilename()).'/Resources/translations';
        }

        if (class_exists('Symfony\Component\Form\Form')) {
            $r = new \ReflectionClass('Symfony\Component\Form\Form');

            $locations[] = dirname($r->getFilename()).'/Resources/translations';
        }

        if (class_exists('Symfony\Component\Security\Core\Exception\AuthenticationException')) {
            $r = new \ReflectionClass('Symfony\Component\Security\Core\Exception\AuthenticationException');

            if (file_exists($dir = dirname($r->getFilename()).'/../../Resources/translations')) {
                $locations[] = $dir;
            } else {
                // Symfony 2.4 and above
                $locations[] = dirname($r->getFilename()).'/../Resources/translations';
            }
        }

        $overridePath = $this->kernel->getRootDir() . '/Resources/%s/translations';
        foreach ($this->kernel->getBundles() as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/translations')) {
                $locations[] = $dir;
            }
            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $locations[] = $dir;
            }
        }

        if (is_dir($dir = $this->kernel->getRootDir() . '/Resources/translations')) {
            $locations[] = $dir;
        }

        return $locations;
    }
}
