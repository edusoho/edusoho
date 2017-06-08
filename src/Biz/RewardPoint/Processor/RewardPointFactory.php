<?php

namespace Biz\RewardPoint\Processor;

use AppBundle\Common\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Context\BizAware;

class RewardPointFactory extends BizAware
{
    public function create($type)
    {
        $type = 'reward_point.'.$type;

        if (!isset($this->biz[$type])) {
            throw new InvalidArgumentException(sprintf('Unknown reward_point type %s', $type));
        }

        return $this->biz[$type];
    }
}
