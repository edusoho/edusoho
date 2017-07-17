<?php
namespace Biz\RewardPoint\Setting;

class RewardPointSetting
{
    public function getSetting()
    {
        $generalSetting = array(
            'enable' => 0,
            'name' => '积分',
            'allowTeacherSet' => 0,
        );

        $rules = $this->getRules();



        $setting = array_merge($generalSetting, $rules);

        return $setting;
    }

    protected function getRules()
    {
        return array(
            'create_question' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'reply_question' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'create_discussion' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'reply_discussion' => array('enable' => 0, 'amount' => 0, 'daily_limit' => 0),
            'elite_thread' => array('enable' => 0, 'amount' => 0),
            'appraise_course_classroom' => array('enable' => 0, 'amount' => 0),
            'daily_login' => array('enable' => 0, 'amount' => 0),
        );
    }
}