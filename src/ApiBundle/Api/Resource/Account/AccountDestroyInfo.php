<?php

namespace ApiBundle\Api\Resource\Account;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
use Biz\System\Service\SettingService;

class AccountDestroyInfo extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $record = $this->getDestroyAccountRecordService()->getLastAuditDestroyAccountRecordByUserId($user['id']);
        $site = $this->getSettingService()->get('site');
        $siteName = empty($site['name']) ? '本网校' : $site['name'];

        return array(
            'applied' => empty($record) ? false : true,
            'agreement' => $this->getAgreement($siteName),
        );
    }

    protected function getAgreement($siteName)
    {
        return "<h5>在您申请注销{$siteName}帐号之前,请您仔细阅读并同意本《帐号注销协议》。{$siteName}在此善意提醒您,注销帐号为不可恢复的操作,帐号注销后您将无法再使用该帐号或找回您加入或购买的课程和班级，以及浏览、收藏和发布的任何内容或信息(即使您使用相同的手机号码再次注册并使用{$siteName})，建议您在注销前自行备份注销帐号的相关信息,并请确认与该帐号相关的所有服务均已进行妥善处理。注销成功后,我们将按照《{$siteName}隐私政策》相关条款删除您的个人信息,或对其进行匿名化处理。请您知悉并理解,根据相关法律法规规定{$siteName}将保留该帐号的相关网络日志记录不少于6个月的时间;您在平台上的的交易记录将保留不少于3年。<br>
            一、非常遗憾我们无法继续为您提供服务。如您仍选择继续注销帐号,则该帐号内的虚拟权益等财产性利益(包括但不限于以下权益)视为您自动放弃,将无法继续使用。您理解并同意, {$siteName}无法协助您重新恢复上述服务。<br>
            1、您将放弃未到期的会员权益、未使用的各类优惠券、学习卡、您已购买的课程和班级，您在{$siteName}上获得的虚拟币和积分以及其他已经产生但未消耗完毕的权益或未来预期的利益;<br>
            2、您将解除该帐号与其他产品(例如独立题库、分销中心等）的绑定或授权登录关系；<br>
            3、该帐号的全部个人资料和历史信息(包括但不限于头像、用户名、发布内容、浏览记录、收藏等)将无法找回;<br>
            4、该帐号通过分销以及分享有礼服务所获得的佣金将无法提现。<br>
            二、为了帮助您完成帐号注销,您进一步向{$siteName}声明与保证:<br>
            1、该帐号系通过官方渠道注册且为您本人的帐号;<br>
            2、该帐号内无您参与的但尚未完成的拼团活动；<br>
            三、在您的帐号注销期间,如果您的帐号涉及争议纠纷,包括但不限于投诉、举报、诉讼、仲裁、国家有权机关调查等,您知晓并理解{$siteName}有权自行决定是否终止本帐号的注销而无需另行得到您的同意。<br>
            四、您注销本帐号并不代表注销前该帐号中的行为和相关责任得到减轻或豁免。</h5>";
    }

    /**
     * @return DestroyAccountRecordService
     */
    private function getDestroyAccountRecordService()
    {
        return $this->service('DestroyAccount:DestroyAccountRecordService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
