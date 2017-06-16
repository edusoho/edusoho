import notify from "common/notify";

$(document).ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions){
  
  let data = $.parseJSON(XMLHttpRequest.getResponseHeader('Reward-Point-Notify'));

  if (data) {
    let message = Translator.trans('积分');

    if (data.type == 'inflow') {
      message = message+'+'+data.amount;
    } else {
      message = message+'-'+data.amount;
    };

    if (data.way == 'create_question') {
      message = message+'【发布1个话题】';
    } else if (data.way == 'reply_question') {
      message = message+'【回复1个话题】';
    } else if (data.way == 'create_discussion') {
      message = message+'【提出1个问题】';
    } else if (data.way == 'reply_discussion') {
      message = message+'【回复1个问题】';
    } else if (data.way == 'elite_thread') {
      message = message+'【话题被加精】';
    } else if (data.way == 'appraise_course_classroom') {
      message = message+'【评价成功】';
    } else if (data.way == 'daily_login') {
      message = message+'【日常登陆】';
    }

    notify('success', message);
  };

});

if ($('#rewardPointNotify').length > 0) {
  let data = $.parseJSON($('#rewardPointNotify').text());

  if (data) {
    let message = Translator.trans('积分');

    if (data.type == 'inflow') {
      message = message+'+'+data.amount;
    } else {
      message = message+'-'+data.amount;
    };

    if (data.way == 'create_question') {
      message = message+'【发布1个话题】';
    } else if (data.way == 'reply_question') {
      message = message+'【回复1个话题】';
    } else if (data.way == 'create_discussion') {
      message = message+'【提出1个问题】';
    } else if (data.way == 'reply_discussion') {
      message = message+'【回复1个问题】';
    } else if (data.way == 'elite_thread') {
      message = message+'【话题被加精】';
    } else if (data.way == 'appraise_course_classroom') {
      message = message+'【评价成功】';
    } else if (data.way == 'daily_login') {
      message = message+'【日常登陆】';
    }

    notify('success', message);
  };
};