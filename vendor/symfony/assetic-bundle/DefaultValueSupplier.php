<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle;

use Assetic\ValueSupplierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default Value Supplier.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultValueSupplier implements ValueSupplierInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getValues()
    {
        $request = $this->getCurrentRequest();

        if (!$request) {
            return array();
        }

        return array(
            'locale' => $request->getLocale(),
            'env'    => $this->container->getParameter('kernel.environment'),
        );
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    private function getCurrentRequest()
    {
        $request = null;
        $requestStack = $this->container->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if ($requestStack) {
            $request = $requestStack->getCurrentRequest();
        } elseif ($this->container->isScopeActive('request')) {
            $request = $this->container->get('request');
        }

        return $request;
    }
}
