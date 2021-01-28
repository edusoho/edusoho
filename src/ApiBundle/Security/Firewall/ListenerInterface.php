<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;

interface ListenerInterface
{
    public function handle(Request $request);
}
