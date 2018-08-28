class PPT {
  constructor() {
    this.init();
  }
  init() {
    this.initSelect();
    this.initStep3Form();
    window.ltc.on('getFinishCondition', function(msg){
      window.ltc.emit('returnFinishCondition', {valid:true,data: window.ltc.getFormSerializeObject($('#step3-form'))});
    });
  }

  initStep3Form() {
    var $step3_form = $('#step3-form');
    this.validator3 = $step3_form.validate({
      rules: {
        finishDetail: {
          required: ()=> {
            return $('#condition-select').children('option:selected').val() === 'time';
          },
          positive_integer: true,
          max: 300,
          min: 1,
        },
      },
      messages: {
        finishDetail: {
          required: Translator.trans('activity.ppt_manage.finish_detail_required_error_hint'),
        },
      }
    });
    $step3_form.data('validator', this.validator3);
  }

  initSelect() {
    let $select = $('#condition-select');
    if ($select.children('option:selected').val() === 'time') {
      this.initStep3Form();
    }

    $select.on('change', event => {
      let conditionsType = $(event.currentTarget).children('option:selected').val();
      let $conditionsDetail = $('#condition-group');
      if (conditionsType !== 'time') {
        $conditionsDetail.addClass('hidden');
        return;
      }else {
        $conditionsDetail.removeClass('hidden');
      }
      if(!this.validator3) {
        this.initStep3Form();
      }
    });
  }

  _serializeArray($e) {
    let o = {};
    let a = $e.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  }
}

new PPT();