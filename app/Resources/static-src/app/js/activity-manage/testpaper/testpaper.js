import { dateFormat } from '../../../common/unit';

export default class Testpaper {
  constructor($element) {
    this.$element = $element;
    this.$step2_form  = this.$element.find('#step2-form');
    this.$step3_form  = this.$element.find('#step3-form');
    this.$parentiframe = $(window.parent.document).find('#task-create-content-iframe');
    this.scoreSlider = null;
    this._init();
  }

  _init() {
    dateFormat();
    this.setValidateRule();
    this.initEvent();
    this.initStepForm2();
  }

  initEvent() {
    this.$element.find('#testpaper-media').on('change', event=>this.changeTestpaper(event));
    this.$element.find('input[name=doTimes]').on('change', event=>this.showRedoInterval(event));
    this.$element.find('input[name="testMode"]').on('change',event=>this.startTimeCheck(event));
    this.$element.find('input[name="length"]').on('blur',event=>this.changeEndTime(event));
    this.$element.find('#condition-select').on('change',event=>this.changeCondition(event));
    this.initSelectTestpaper(this.$element.find('#testpaper-media').find('option:selected'),$('[name="finishScore"]').val());
  }

  setValidateRule() {
    $.validator.addMethod('arithmeticFloat',function(value,element){  
      return this.optional( element ) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));

    $.validator.addMethod('positiveInteger',function(value,element){  
      return this.optional( element ) || /^[1-9]\d*$/.test(value);
    }, $.validator.format(Translator.trans('activity.testpaper_manage.positive_integer_error_hint')));

    
  }

  initStepForm2() {
    var validator = this.$step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required:true,
          trim: true,
          maxlength: 50,
          course_title: true,
        },
        mediaId: {
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
        mediaId: {
          required:Translator.trans('activity.testpaper_manage.media_error_hint'),
        },
        redoInterval: {
          max: Translator.trans('activity.testpaper_manage.max_error_hint')
        },
      }
    });
    this.$step2_form.data('validator',validator);
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
      $('.js-score-total').text(score);
      if(!$('input[name="title"]').val()) {
        $('input[name="title"]').val($option.text());
      }
      this.initScoreSlider(parseInt(passScore),parseInt(score));
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
      this.dateTimePicker();
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
      //$('input[name="startTime"]').val('0');
    }
  }

  changeEndTime(event) {
    let startTime = $('input[name="startTime"]:visible').val();
    // if (startTime) {
    //   this.showEndTime(Date.parse(startTime));
    // }
  }

  changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value!='score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  initScoreSlider(passScore,score) {
    let scoreSlider = document.getElementById('score-slider');
    let option = {
      start: passScore,
      connect: [true, false],
      tooltips: [true],
      step: 1,
      range: {
        'min': 0,
        'max': score
      }
    };
    if(this.scoreSlider) {
      this.scoreSlider.updateOptions(option);
    }else {
      this.scoreSlider = noUiSlider.create(scoreSlider,option);
      scoreSlider.noUiSlider.on('update', function( values, handle ){
        $('.noUi-tooltip').text(`${(values[handle]/score*100).toFixed(0)}%`);
        $('.js-score-tooltip').css('left',`${(values[handle]/score*100).toFixed(0)}%`);
        $('.js-passScore').text(parseInt(values[handle]));
        $('input[name="finishScore"]').val(parseInt(values[handle]));
      });
    }
    
    let tooltipInnerText = Translator.trans('activity.testpaper_manage.pass_score_hint', {'passScore': '<span class="js-passScore">'+passScore+'</span>'});
    let html = `<div class="score-tooltip js-score-tooltip"><div class="tooltip top" role="tooltip" style="">
      <div class="tooltip-arrow"></div>
      <div class="tooltip-inner ">
        ${tooltipInnerText}
      </div>
      </div></div>`;
    $('.noUi-handle').append(html);
    $('.noUi-tooltip').text(`${(passScore/score*100).toFixed(0)}%`);
    $('.js-score-tooltip').css('left',`${(passScore/score*100).toFixed(0)}%`);
  }

  getItemsTable(url, testpaperId) {
    $.post(url, {testpaperId:testpaperId},function(html){
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  dateTimePicker() {
    let data = new Date();
    let $starttime = $('input[name="startTime"]');

    if ($starttime.is(':visible') && ($starttime.val() == '' || $starttime.val() == '0')) {
      $starttime.val(data.Format('yyyy-MM-dd hh:mm'));
    }
   
    $starttime.datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      minView: 'hour',
      endDate: new Date(Date.now() + 86400 * 365 * 10 *1000)
    })
      .on('show', event => {
        this.$parentiframe.height($('body').height() + 240);
      })
      .on('hide', event => {
        this.$step2_form.data('validator').form();
        this.$parentiframe.height($('body').height());
      })
      .on('changeDate',event =>{
        let date = event.date.valueOf();
        // this.showEndTime(date);
      });
    $starttime.datetimepicker('setStartDate',data);
    // this.showEndTime(Date.parse($starttime.val()));
  }

  // showEndTime(date) {
  //   let limitedTime = $('input[name="limitedTime"]').val();
  //   if (limitedTime != 0) {
  //     let endTime = new Date(date + limitedTime * 60 * 1000);
  //     let endDate = endTime.Format("yyyy-MM-dd hh:mm");
  //     $('#starttime-show').html(endDate);
  //     $('.endtime-input').removeClass('hidden');
  //     $('input[name="endTime"]').val(endDate);
  //   }else {
  //     $('.endtime-input').addClass('hidden');
  //   }
  // }
}