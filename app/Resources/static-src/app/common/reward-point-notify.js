import notify from "common/notify";

$(document).ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions){
  let message = transformMessage(XMLHttpRequest.getResponseHeader('Reward-Point-Notify'));
  if (message) {
    notify('success', message);
  };
});

if ($('#rewardPointNotify').length > 0) {
  let message = transformMessage($('#rewardPointNotify').text());
  if (message) {
    notify('success', message);
  };
};

function transformMessage (param) {
  let data = $.parseJSON(param);

  if (data) {
    let message = Translator.trans('积分');

    message = transformMessageAccountPart(message, data.type, data.amount);

    message = transformMessageWayPart(message, data.way);

    return message;
  }

  return null;
}

function transformMessageAccountPart (message, type, amount) {
  if (type == 'inflow') {
    message = message+'+'+amount;
  } else {
    message = message+'-'+amount;
  };

  return message;
}

function transformMessageWayPart (message, way) {
  if (way == 'create_question') {
    message = message+'【发布1个问题】';
  } else if (way == 'reply_question') {
    message = message+'【回复1个问题】';
  } else if (way == 'create_discussion') {
    message = message+'【提出1个话题】';
  } else if (way == 'reply_discussion') {
    message = message+'【回复1个话题】';
  } else if (way == 'elite_thread') {
    message = message+'【话题被加精】';
  } else if (way == 'appraise_course_classroom') {
    message = message+'【评价成功】';
  } else if (way == 'daily_login') {
    message = message+'【日常登录】';
  } else if (way == 'task_reward_point') {
    message = message+'【完成任务】';
  }

  return message;
}