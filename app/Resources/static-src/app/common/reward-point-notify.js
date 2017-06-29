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
    let message = Translator.trans('notify.reward_point');

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
    message = message+'【'+Translator.trans('notify.reward_point.create_question')+'】';
  } else if (way == 'reply_question') {
    message = message+'【'+Translator.trans('notify.reward_point.reply_question')+'】';
  } else if (way == 'create_discussion') {
    message = message+'【'+Translator.trans('notify.reward_point.create_discussion')+'】';
  } else if (way == 'reply_discussion') {
    message = message+'【'+Translator.trans('notify.reward_point.reply_discussion')+'】';
  } else if (way == 'elite_thread') {
    message = message+'【'+Translator.trans('notify.reward_point.elite_thread')+'】';
  } else if (way == 'appraise_course_classroom') {
    message = message+'【'+Translator.trans('notify.reward_point.appraise_course_classroom')+'】';
  } else if (way == 'daily_login') {
    message = message+'【'+Translator.trans('notify.reward_point.daily_login')+'】';
  } else if (way == 'task_reward_point') {
    message = message+'【'+Translator.trans('notify.reward_point.task_reward_point')+'】';
  }

  return message;
}