export default class Finish {
  constructor() {
    this.$mediaId = $('[name="mediaId"]');
    this.init();
  }

  init() {
    this.initStep3Form();
    this.initEvent();
  }

  initEvent() {
    window.ltc.on('getFinishCondition', function(msg){
      window.ltc.emit('returnFinishCondition', {valid:true,data:window.ltc.getFormSerializeObject($('#step3-form'))});
    });
  }

  initStep3Form() {
    let $step3_form = $('#step3-form');
    let validator = $step3_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
        },
        finishDetail: {
          required: true,
          positive_integer: true,
          max: 300,
          min: 1,
        },
      },
      messages: {
        finishDetail: {
          required: Translator.trans('activity.audio_manage.finish_detail_required_error_hint'),
          digits: Translator.trans('activity.audio_manage.finish_detail_digits_error_hint')
        }
      }
    });
  }
}