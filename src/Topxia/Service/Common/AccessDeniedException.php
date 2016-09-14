<?php
namespace Topxia\Service\Common;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @deprecated the AccessDeniedException is deprecated and will be removed. Please use use `throw new Topxia\Service\Common\Exception\XXXException(...)` instead.
 */
class AccessDeniedException extends AccessDeniedHttpException
{
}
