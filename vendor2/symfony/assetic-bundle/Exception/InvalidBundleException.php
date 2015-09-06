<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Exception;

class InvalidBundleException extends \LogicException
{
    public function __construct($bundle, $usage, $template, array $enabled, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('You must add %s to the assetic.bundle config to use %s in %s.', $bundle, $usage, $template);

        if ($enabled) {
            $message .= sprintf(' (currently enabled: %s)', implode(', ', $enabled));
        }

        parent::__construct($message, $code, $previous);
    }
}
