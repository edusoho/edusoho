<?php
namespace Biz\Testpaper\Builder;

use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\InvalidArgumentException;

class TestpaperBuilderFactory
{
    public static function create(Biz $biz, $name)
    {
        if (!in_array($name, array('testpaper', 'homework', 'exercise'))) {
            throw new InvalidArgumentException(sprintf('Unknown testpaper type: %s', $name));
        }

        $class = __NAMESPACE__.'\\'.ucfirst($name).'Builder';
        return new $class($biz);
    }
}
