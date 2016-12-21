<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Config;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

/**
 * Implements SelfCheckingResourceInterface required in Symfony 3.0.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 *
 * @internal
 */
class SelfCheckingAsseticResource extends AsseticResource implements SelfCheckingResourceInterface
{
}
