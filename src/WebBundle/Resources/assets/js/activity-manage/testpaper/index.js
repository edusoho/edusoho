class Testpaper {
	constructor($form) {
		this.$element = $form;
    this.slider = null;
		this._setValidateRule();
    this._init();
    this._initEvent();
    this._initSlider();

  }

  _init() {
    this._inItStep2form();
    this._dateTimePicker();

    let testpaperId = $('#testpaper-media').find('option:selected').val();
    if (testpaperId != 0) {
    	this._getItemsTable($('#testpaper-media').data('getTestpaperItems'), testpaperId);
    }
  }

  _initEvent() {
  	$('#testpaper-media').on('change', event=>this._changeTestpaper(event));
  	$('input[name=doTimes]').on('change', event=>this._showRedoInterval(event));
  	$('input[name="testMode"]').on('change',event=>this._startTimeCheck(event))
    $('input[name="limitedTime"]').on('blur',event=>this._changeEndTime(event));
    $('#condition-select').on('change',event=>this._changeCondition(event));

  }

  _initSlider() {
    this.slider = $('.nstSlider').nstSlider({
      "left_grip_selector": ".leftGrip",
      "value_changed_callback": function(cause, leftValue, rightValue) {
        let $list = $(this).closest('.nstSlider-list');
        let left = $('.js-leftGrip').css('left');
        let total = $list.find('.js-totale-text').text();
        let value = Math.floor(leftValue/total*100)+'%';
        $list.find('.js-leftGrip-remask-text').text(leftValue);
        $list.find('.js-bar').css('width',value);
        $list.find('.js-nstSlider-content').css('left',left);
        $list.find('.js-leftGrip-text').css('left',left).text(value);
      }
    });

    $(document).mousemove(function() {
      window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();;
    });
    let defaultValue = $('.js-totale-text').text()*0.6;
  }

  _changeCondition(event) {
    var $this = $(event.currentTarget);
    var value = $this.find('option:selected').val();
    if(value!='score') {
      $('.js-score-slider').addClass('hidden');
    }else {
      $('.js-score-slider').removeClass('hidden');
    }
  }

  _changeTestpaper(event) {
  	var $this = $(event.currentTarget);
    var mediaId = $this.find('option:selected').val();
    
    if (mediaId != 0) {
      this._getItemsTable($this.data('getTestpaperItems'), mediaId);
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
    var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');

    $starttime.datetimepicker({
        autoclose: true,
        format: 'yyyy-mm-dd hh:ii',
        language:"zh",
        minView: 'hour'
    })
    .on('show', function(ev){
      $parentiframe.height($parentiframe.contents().find('body').height()+260);
    })
    .on('hide', function(ev){
      $parentiframe.height($parentiframe.contents().find('body').height());
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

    if (startTime != '') {
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