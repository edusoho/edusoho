import notify from 'common/notify';
const $timeLeft = $('.js-time-left');
const $smsCode = $('.js-sms-send');
const $fetchBtnText = $('.js-fetch-btn-text');

export const countDown = (num) => {
  $timeLeft.html(num);
  $fetchBtnText.html(Translator.trans('site.data.get_sms_code_again_btn'));
  notify('success', Translator.trans('site.data.get_sms_code_success_hint'));
  refreshTimeLeft();
};

const refreshTimeLeft = () => {
  let leftTime = $timeLeft.text();
  $timeLeft.html(leftTime - 1);
  if (leftTime - 1 > 0) {
    $smsCode.attr('disabled', true);
    setTimeout(refreshTimeLeft, 1000);
  } else {
    $timeLeft.html('');
    $fetchBtnText.html(Translator.trans('oauth.send.validate_message'));
    $smsCode.removeAttr('disabled');
  }
};