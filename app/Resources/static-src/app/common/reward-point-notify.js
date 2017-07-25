import notify from "common/notify";

export default class RewardPointNotify {

  STORAGE_NAME = 'reward-point-notify-queue';

  constructor() {
    this.storage = window.localStorage;
    this.init();
  }

  init() {
    let storageStr = this.storage.getItem(this.STORAGE_NAME);
    if (!storageStr) {
      this.stack = [];
    } else {
      this.stack = JSON.parse(storageStr);
    }
  }

  display() {

    if (this.stack.length > 0) {
      notify('success', this.stack.pop());
      this.store();
    } else {
      console.log('Nothing to display');
    }
  }

  store() {
    this.storage.setItem(this.STORAGE_NAME, JSON.stringify(this.stack));
  }

  push(item) {
    if (item) {
      this.stack.push(item);
      this.store();
    }
  }

  size() {
    return this.stack.size();
  }

}


// $(document).ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions){
//   let message = transformMessage(XMLHttpRequest.getResponseHeader('Reward-Point-Notify'));
//   if (message) {
//     notify('success', message);
//   };
// });
//
// if ($('#rewardPointNotify').length > 0) {
//   let message = transformMessage($('#rewardPointNotify').text());
//   if (message) {
//     notify('success', message);
//   };
// };
//
// function transformMessage (param) {
//   let data = $.parseJSON(param);
//
//   if (data) {
//     let message = Translator.trans($('#rewardPointName').text());
//
//     message = transformMessageAccountPart(message, data.type, data.amount);
//
//     message = transformMessageWayPart(message, data.way);
//
//     return message;
//   }
//
//   return null;
// }
//
// function transformMessageAccountPart (message, type, amount) {
//   if (type == 'inflow') {
//     message = message+'+'+amount;
//   } else {
//     message = message+'-'+amount;
//   };
//
//   return message;
// }
//
// function transformMessageWayPart (message, way) {
//   if (way == 'create_question') {
//     message = message+'【'+Translator.trans('notify.reward_point.create_question')+'】';
//   } else if (way == 'reply_question') {
//     message = message+'【'+Translator.trans('notify.reward_point.reply_question')+'】';
//   } else if (way == 'create_discussion') {
//     message = message+'【'+Translator.trans('notify.reward_point.create_discussion')+'】';
//   } else if (way == 'reply_discussion') {
//     message = message+'【'+Translator.trans('notify.reward_point.reply_discussion')+'】';
//   } else if (way == 'elite_thread') {
//     message = message+'【'+Translator.trans('notify.reward_point.elite_thread')+'】';
//   } else if (way == 'appraise_course_classroom') {
//     message = message+'【'+Translator.trans('notify.reward_point.appraise_course_classroom')+'】';
//   } else if (way == 'daily_login') {
//     message = message+'【'+Translator.trans('notify.reward_point.daily_login')+'】';
//   } else if (way == 'task_reward_point') {
//     message = message+'【'+Translator.trans('notify.reward_point.task_reward_point')+'】';
//   }
//
//   return message;
// }