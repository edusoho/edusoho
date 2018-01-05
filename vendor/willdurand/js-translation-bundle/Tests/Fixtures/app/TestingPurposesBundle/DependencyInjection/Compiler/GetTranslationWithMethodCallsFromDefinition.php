<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests\Fixtures\app\TestingPurposesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * @author Hugo MONTEIRO <hugo.monteiro@gmail.com>
 */
class GetTranslationWithMethodCallsFromDefinition implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $translationFile = __DIR__ .'/../../Resources/translations/bar.en.yml';
        $translator = $container->findDefinition('translator.default');

        list($domain, $locale, $format) = explode('.', $translationFile, 3);
        $translator->addMethodCall(
            'addResource',
            array($format, $translationFile, $locale , $domain)
        );
    }
}
