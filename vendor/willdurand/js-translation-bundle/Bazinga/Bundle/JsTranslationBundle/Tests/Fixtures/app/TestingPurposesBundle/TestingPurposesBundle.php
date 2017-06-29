<?php
namespace Bazinga\Bundle\JsTranslationBundle\Tests\Fixtures\app\TestingPurposesBundle;

use Bazinga\Bundle\JsTranslationBundle\Tests\Fixtures\app\TestingPurposesBundle\DependencyInjection\Compiler\GetTranslationWithMethodCallsFromDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TestingPurposesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GetTranslationWithMethodCallsFromDefinition());
    }
}
