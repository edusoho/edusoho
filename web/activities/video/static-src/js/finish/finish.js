
export default class Finish {
  constructor() {
    this.isInitStep3from();
    this.initEvent();
    this.getContentData();
  }

  getContentData() {
    window.ltc.emit('getContentData');
  }

  initEvent() {
    window.ltc.on('getFinishCondition', function(msg){
      window.ltc.emit('returnFinishCondition', {valid:true,data:window.ltc.getFormSerializeObject($('#step3-form'))});
    });

    window.ltc.on('returnContentData', (msg) => {
      this.displayFinishCondition(msg);
    });
  }

  displayFinishCondition(contentData) {
    if (contentData.media) {
      let media = JSON.parse(contentData.media);
      if (media.source === 'self') {
        $('#finish-condition option[value=end]').removeAttr('disabled');
        $('#finish-condition option[value=end]').text(Translator.trans('activity.video_manage.finish_detail'));
      } else {
        $('#finish-condition option[value=end]').text(Translator.trans('activity.video_manage.other_finish_detail'));
        $('#finish-condition option[value=end]').attr('disabled', 'disabled');
        $('#finish-condition option[value=time]').attr('selected', false);
        $('#finish-condition option[value=time]').attr('selected', true);
        $('.viewLength').removeClass('hidden');
        this.initStep3from();
      }
    }

  }

  initStep3from() {
    var $step3_forom = $('#step3-form');
    var validator = $step3_forom.data('validator');
    $step3_forom.validate({
      rules: {
        'ext[finishDetail]': {
          required: true,
          positive_integer: true,
          max: 300,
          min: 1,
        }
      },
      messages: {
        'ext[finishDetail]': {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
        }
      }
    });
    $step3_forom.data('validator', validator);
  }

  isInitStep3from() {
    // 完成条件是观看时长的情况
    if ($('#finish-condition').children('option:selected').val() === 'time') {
      $('.viewLength').removeClass('hidden');
      this.initStep3from();
    }

    $('#finish-condition').on('change', (event) => {
      if (event.target.value == 'time') {
        $('.viewLength').removeClass('hidden');
        this.initStep3from();
      } else {
        $('.viewLength').addClass('hidden');
        $('input[name="ext[finishDetail]"]').rules('remove');
      }
    });
  }
}