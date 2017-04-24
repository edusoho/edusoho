<?php

namespace AppBundle\Controller\Admin;

class BaseController extends \AppBundle\Controller\BaseController
{
    /**
     * condtions 中添加 likeOrgCode.
     *
     * @param [type] $conditions [description]
     *
     * @return [type] [description]
     */
    protected function fillOrgCode($conditions)
    {
        if ($this->setting('magic.enable_org')) {
            if (!isset($conditions['orgCode'])) {
                $conditions['likeOrgCode'] = $this->getUser()->getSelectOrgCode();
            } else {
                $conditions['likeOrgCode'] = $conditions['orgCode'];
                unset($conditions['orgCode']);
            }
        } else {
            if (isset($conditions['orgCode'])) {
                unset($conditions['orgCode']);
            }
        }

        return $conditions;
    }
}
