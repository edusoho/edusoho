<?php
namespace Biz\Testpaper\Pattern;

use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\InvalidArgumentException;

class TestpaperPatternFactory
{
    public static function create(Biz $biz, $name)
    {
        if (!in_array($name, array('questionType'))) {
            throw new InvalidArgumentException(sprintf('Unknown testpaper pattern type: %s', $name));
        }

        $class = __NAMESPACE__.'\\'.ucfirst($name).'Pattern';
        return new $class($biz);
    }
}
