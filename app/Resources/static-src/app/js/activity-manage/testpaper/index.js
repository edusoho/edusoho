import { dateFormat } from '../../../common/unit';

class Testpaper {
	constructor($element) {
		this.$element = $element;
    this.$step2_form  = this.$element.find('#step2-form');
    this.$step3_form  = this.$element.find('#step3-form');
    this.$parentiframe = $(window.parent.document).find('#task-create-content-iframe');
    this._init();
  }

  _init() {
    dateFormat();
    this.setValidateRule();
    this.initEvent();
    this.initStepForm2();
    this.initSelectTestpaper($('#testpaper-media').find('option:selected'),$('[name="finishScore"]').val());
  }

  initEvent() {
  	this.$element.find('#testpaper-media').on('change', event=>this.changeTestpaper(event));
  	this.$element.find('input[name=doTimes]').on('change', event=>this.showRedoInterval(event));
  	this.$element.find('input[name="testMode"]').on('change',event=>this.startTimeCheck(event))
    this.$element.find('input[name="limitedTime"]').on('blur',event=>this.changeEndTime(event));
    this.$element.find('#condition-select').on('change',event=>this.changeCondition(event));
  }

  setValidateRule() {
    $.validator.addMethod("arithmeticFloat",function(value,element){  
      return this.optional( element ) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format("必须为正数，保留一位小数"));

    $.validator.addMethod("positiveInteger",function(value,element){  
      return this.optional( element ) || /^[1-9]\d*$/.test(value);
    }, $.validator.format("必须为正整数"));

    $.validator.addMethod("DateAndTime",function(value,element){  
      let reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
      return this.optional( element ) || reg.test(value);
    }, $.validator.format("请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm"));
  }

  initStepForm2() {
    var validator = this.$step2_form.validate({
        onkeyup: false,
        rules: {
            title: {
              required:true
            },
            mediaId: {
              required: true,
              digits:true
            },
            limitedTime:{
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
                return $('[name="doTimes"]:checked').val() == 1;
              },
              arithmeticFloat:true,
              max:1000000000
            }
        },
        messages: {
            title:{
              required:"请填写标题"
            },
            mediaId: {
              required:"请选择试卷"
            },
            startTime: {
              required:"请选择考试的开始时间"
            },
            redoInterval: {
              max: "最大值不能超过1000000000"
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
      console.log(passScore);
      $('#score-single-input').val(passScore);
      $('.js-score-total').text(score);
      this.initSlider(passScore,score);
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
    var $this = $(event.currentTarget);
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

  changeEndTime() {
    let $this = $(event.currentTarget);
    let limitedTime = $this.val();
    let startTime = $('input[name="startTime"]:visible').val();
    console.log(startTime);
    if (startTime) {
      let endTime = new Date(Date.parse(startTime) + limitedTime * 60 * 1000);
      let endDate = endTime.Format("yyyy-MM-dd hh:mm");
      $('input[name="endTime"]').val(endDate);
    }
  }

  changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value!='score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  initSlider(passScore,score) {
    console.log(passScore);
    console.log(score);
    let $sliderRemask = null;
    let scoreTotal = $('#score-single-input').data('score-total');
    let silder = null;
    console.log($('.single-slider'));
    silder = $('.single-slider').jRange({
      from: 0,
      to: score,
      step: 1,
      format: function(value,type) {
        let  v= (parseInt(value) / parseInt(score)).toFixed(2)*100;
        return `${v}%`;
      },
      width: 300,
      showLabels: true,
      showScale: false,
      onstatechange: function (argument,data) {
        $sliderRemask.text(argument);
      }
    });
    console.log(silder);
    console.log($.fn);
    $sliderRemask = $('.js-slider-remask').text(passScore);
  }

  getItemsTable(url, testpaperId) {
  	$.post(url, {testpaperId:testpaperId},function(html){
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  dateTimePicker() {
    let data = new Date();
    let $starttime = $('input[name="startTime"]').val(data.Format('yyyy-MM-dd hh:mm'));
    $starttime.datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd hh:ii',
      language:"zh",
      minView: 'hour'
    })
    .on('show', event => {
      this.$parentiframe.height($('body').height() + 240);
    })
    .on('hide', event => {
      this.$step2_form.data('validator').form();
      this.$parentiframe.height($('body').height());
    })
    .on('changeDate', function(ev){
      let date = ev.date.valueOf();
      let limitedTime = $('input[name="limitedTime"]').val();
      if (limitedTime != 0) {
        let endTime = new Date(date + limitedTime * 60 * 1000);
        let endDate = endTime.Format("yyyy-MM-dd hh:mm");
        $('#starttime-show').html(endDate);
        $('.endtime-input').removeClass('hidden');
        $('input[name="endTime"]').val(endDate);
      }
    });
    $starttime.datetimepicker('setStartDate',data);
  }
}

new Testpaper($('#iframe-content'));