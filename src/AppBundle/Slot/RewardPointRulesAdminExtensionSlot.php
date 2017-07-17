<?php

namespace AppBundle\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;

class RewardPointRulesAdminExtensionSlot extends SlotInjection
{
    public function inject()
    {
        $rules = $this->getRules();
        return $this->container->get('twig')->render('admin/reward-point/slot/reward-point-rules-slot.html.twig', array(
            'rules' => empty($rules) ? '' : $rules,
        ));
    }

    protected function getRules()
    {
        $rules = array(
            'create_question' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'reply_question' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'create_discussion' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'reply_discussion' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'elite_thread' => array('enable' => 0, 'amount' => 0),
            'appraise_course_classroom' => array('enable' => 0, 'amount' => 0),
            'daily_login' => array('enable' => 0, 'amount' => 0),
        );

        $setting = $this->getSettingService()->get('reward_point', array());

        return array_merge($rules, $setting);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }
}