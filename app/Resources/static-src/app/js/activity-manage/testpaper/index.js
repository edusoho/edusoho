class Testpaper {
	constructor($form) {
		this.$element = $form;
    this.slider = null;
    this.$parentiframe = $(window.parent.document).find('#task-create-content-iframe');
		this._setValidateRule();
    this._init();
    this._initEvent();
  }

  _init() {
    this._inItStep2form();
    this._dateTimePicker();
    let passScore = $('[name="finishScore"]').val();
    this._initSelectTestpaper($('#testpaper-media').find('option:selected'),passScore);
  }

  _initEvent() {
  	$('#testpaper-media').on('change', event=>this._changeTestpaper(event));
  	$('input[name=doTimes]').on('change', event=>this._showRedoInterval(event));
  	$('input[name="testMode"]').on('change',event=>this._startTimeCheck(event))
    $('input[name="limitedTime"]').on('blur',event=>this._changeEndTime(event));
    $('#condition-select').on('change',event=>this._changeCondition(event));
  }

  _initSlider(passScore,score) {
    let $sliderRemask = null;
    let scoreTotal = $('#score-single-input').data('score-total');
    $('.single-slider').jRange({
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
    $sliderRemask = $('.js-slider-remask').text(passScore);
  }

  _changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value!='score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  _changeTestpaper(event) {
  	let $target = $(event.currentTarget);
    let $option = $target.find('option:selected');
    
    this._initSelectTestpaper($option);
  }

  _initSelectTestpaper($option, passScore='') {
    let mediaId = $option.val();
    
    if (mediaId != '') {
      this._getItemsTable($option.closest('select').data('getTestpaperItems'), mediaId);
      let score = $option.data('score');
      if (passScore == '') {
        passScore = Math.ceil(score * 0.6);
      }
      $('#score-single-input').val(passScore);
      $('.js-score-total').text(score);
      this._initSlider(passScore,score);
    } else {
      $('#questionItemShowDiv').hide();
    }
  }

  _getItemsTable(url, testpaperId) {
  	$.post(url, {testpaperId:testpaperId},function(html){
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  _showRedoInterval(event) {
    var $this = $(event.currentTarget);

    if ($this.val() == 1) {
      $('#lesson-redo-interval-field').closest('.form-group').hide();
      $('.starttime-check-div').show();
    } else {
      $('#lesson-redo-interval-field').closest('.form-group').show();
      $('.starttime-check-div').hide();
    }
  }

  _startTimeCheck(event) {
  	var $this = $(event.currentTarget);

  	if ($this.val() == 'realTime') {
      $('.starttime-input').show();
    } else {
      $('.starttime-input').hide();
    }
  }

  _dateTimePicker() {
    let $starttime = $('input[name="startTime"]');
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
      this.$parentiframe.height($('body').height());
    })
    .on('changeDate', function(ev){
      let date = ev.date.valueOf();
      let limitedTime = $('input[name="limitedTime"]').val();
      if (limitedTime != 0) {
        let endTime = new Date(date + limitedTime * 60 * 1000);
        let endDate = endTime.getFullYear() + '-' + (endTime.getMonth() + 1) + '-' 
                      + endTime.getDate() + ' ' + endTime.getHours() + ':' + endTime.getMinutes();  
        $('#starttime-show').html(endDate);
        $('.endtime-input').show();
        $('input[name="endTime"]').val(endDate);
      }
    });

    $starttime.datetimepicker('setStartDate',new Date());
  }

  _changeEndTime() {
    let $this = $(event.currentTarget);
    let limitedTime = $this.val();
    let startTime = $('input[name="startTime"]').val();

    if (!startTime) {
      let endTime = new Date(Date.parse(startTime) + limitedTime * 60 * 1000);
      let endDate = endTime.getFullYear() + '-' + (endTime.getMonth() + 1) + '-' 
                    + endTime.getDate() + ' ' + endTime.getHours() + ':' + endTime.getMinutes();  
      $('#starttime-show').html(endDate);
      $('input[name="endTime"]').val(endDate);
    }
  }

  _inItStep2form() {
    var  $step2_form = this.$element;
    var validator = $step2_form.validate({
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
    $step2_form.data('validator',validator);
  }

  _inItStep3form() {
    var $step3_form = $("#step3-form");
    var validator = $step3_form.validate({
      onkeyup: false,
      rules: {
          checkType: {
            required: true,
          },
          finishCondition:{
            required:true
          }
      },
      messages: {
          checkType: "考评方式",
          finishCondition: "请选择完成条件"
      }
    });
    $step3_form.data('validator',validator);
  }

  _setValidateRule() {
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
}

new Testpaper($('#step2-form'));