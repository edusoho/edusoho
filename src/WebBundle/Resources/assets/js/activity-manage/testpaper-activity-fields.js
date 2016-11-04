class Testpaper {
	constructor($form) {
		this.$element = $form;
		this._setValidateRule();
    this._init();
    this._initEvent();
  }

  _init() {
    this._inItStep2form();

    let testpaperId = $('#testpaper-media').find('option:selected').val();
    if (testpaperId != 0) {
    	this._getItemsTable($('#testpaper-media').data('getTestpaperItems'), testpaperId);
    }
  }

  _initEvent() {
  	$('#testpaper-media').on('change', event=>this._changeTestpaper(event));
  	$('input[name=doTimes]').on('change', event=>this._showRedoInterval(event));
  	$('input[name="testMode"]').on('change',event=>this._startTimeCheck(event))
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
      
      var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');
      $parentiframe.height($parentiframe.contents().find('body').height());
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
        format: 'yyyy-mm-dd hh:ii',
        language:"zh",
    });
    $starttime.datetimepicker('setStartDate',new Date());
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
            		return ($('[name="doTimes"]').val() == 1) && ($('[name="testMode"]').val() == 'realTime');
            	},
            	DateAndTime:true
            },
            redoInterval:{
            	required:function(){
            		return $('[name="doTimes"]').val() == 1;
            	},
            	arithmeticFloat:true,
            	max:1000000000
            }
        },
        messages: {
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