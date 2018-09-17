import { dateFormat } from 'app/common/unit.js';

class Testpaper {
  constructor($element) {
    this.$element = $element;
    this.$form = this.$element.find('#step2-form');
    this._init();
  }

  _init() {
    dateFormat();
    this.setValidateRule();
    this.initSelectTestpaper(this.$element.find('#testpaper-media').find('option:selected'),$('[name="finishScore"]').val());
    this.initEvent();
    this.initStepForm2();
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', { valid:this.validator.form(), data: window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator.form(), context: {
        score: $('#testpaper-media').find('option:selected').data('score')
      }});
    });
  }

  setValidateRule() {
    $.validator.addMethod('arithmeticFloat',function(value,element){  
      return this.optional( element ) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));
  }

  initEvent() {
    this.$element.find('#testpaper-media').on('change', event=>this.changeTestpaper(event));
    this.$element.find('input[name=doTimes]').on('change', event=>this.showRedoInterval(event));
    this.$element.find('input[name="testMode"]').on('change',event=>this.startTimeCheck(event));
  }

  initStepForm2() {
    this.validator = this.$form.validate({
      onkeyup: false,
      rules: {
        title: {
          required:true,
          trim: true,
          maxlength: 50,
          course_title: true,
        },
        testpaperId: {
          required: true,
          digits:true
        },
        length:{
          required:true,
          digits:true
        },
        startTime:{
          required:function(){
            return ($('[name="doTimes"]:checked').val() == 1) && ($('[name="testMode"]:checked').val() == 'realTime');
          },
          DateAndTime:function(){
            return ($('[name="doTimes"]:checked').val() == 1) && ($('[name="testMode"]:checked').val() == 'realTime');
          }
        },
        redoInterval:{
          required:function(){
            return $('[name="doTimes"]:checked').val() == 0;
          },
          arithmeticFloat:true,
          max:1000000000
        }
      },
      messages: {
        testpaperId: {
          required:Translator.trans('activity.testpaper_manage.media_error_hint'),
        },
        redoInterval: {
          max: Translator.trans('activity.testpaper_manage.max_error_hint')
        },
      }
    });
  }

  initSelectTestpaper($option, passScore='') {
    let mediaId = $option.val();
    if (mediaId != '') {
      this.getItemsTable($option.closest('select').data('getTestpaperItems'), mediaId);
      let score = $option.data('score');
      if (passScore == '') {
        passScore = Math.ceil(score * 0.6);
      }
      $('#score-single-input').val(passScore);
      if(!$('input[name="title"]').val()) {
        $('input[name="title"]').val($option.text());
      }
    } else {
      $('#questionItemShowDiv').hide();
    }
  }

  changeTestpaper(event) {
    let $target = $(event.currentTarget);
    let $option = $target.find('option:selected');
    this.initSelectTestpaper($option);
  }

  showRedoInterval(event) {
    let $this = $(event.currentTarget);
    if ($this.val() == 1) {
      $('#lesson-redo-interval-field').closest('.form-group').hide();
      $('.starttime-check-div').show();
    } else {
      $('#lesson-redo-interval-field').closest('.form-group').show();
      $('.starttime-check-div').hide();
    }
  }

  startTimeCheck(event) {
    var $this = $(event.currentTarget);

    if ($this.val() == 'realTime') {
      $('.starttime-input').removeClass('hidden');
      this.dateTimePicker();
    } else {
      $('.starttime-input').addClass('hidden');
    }
  }

  changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value!='score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  getItemsTable(url, testpaperId) {
    $.post(url, {testpaperId:testpaperId},function(html){
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  dateTimePicker() {
    let data = new Date();
    let $starttime = $('#startTime');
    if ($starttime.is(':visible') && ($starttime.val() == '' || $starttime.val() == '0')) {
      $starttime.val(data.Format('yyyy-MM-dd hh:mm'));
    }
    $starttime.datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      minView: 'hour',
      endDate: new Date(Date.now() + 86400 * 365 * 10 *1000)
    }).on('show', event => {
      this.$form.height(this.$form.height() + 270);
    })
      .on('hide', event => {
        this.validator.form();
        this.$form.height(this.$form.height() - 270);
      })
      .on('changeDate',event =>{
      });
    $starttime.datetimepicker('setStartDate', data);
  }
}

new Testpaper($('#iframe-content'));