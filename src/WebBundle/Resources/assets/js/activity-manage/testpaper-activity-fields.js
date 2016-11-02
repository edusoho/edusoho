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
    this._getItemsTable($('#testpaper-media').data('getTestpaperItems'), testpaperId);
  }

  _initEvent() {
  	$('#testpaper-media').on('change', event=>this._changeTestpaper(event));
  	$('input[name=doTimes]').on('change', event=>this._showRedoInterval(event));
  	$('input[name="testMode"]').on('change',event=>this._startTimeCheck(event))
  }

  _changeTestpaper(event) {
  	var $this = $(event.currentTarget);
    var mediaId = $this.find('option:selected').val();
    
    if (mediaId != '') {
        this.$element.find('#activity-title').val($this.find('option:selected').text());
        this._getItemsTable($this.data('getTestpaperItems'), mediaId);
    } else {
        this.$element.find('#activity-title').val('');
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
            title: {
            	required:true,
            	maxlength:30
            },
            startTime:{
            	required:function(){
            		return ($('[name="doTimes"]').val() == 1) && ($('[name="startTimeCheck"]').val() == 1);
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
            title: "请输入标题",
        }
    });
    $step2_form.data('validator',validator);
  }

  _inItStep3form() {
    var $step3_form = $("#step3-form");
    var validator = $step3_form.validate({
        onkeyup: false,
        rules: {
            'condition_detail': {
                required: true,
            },
        },
        messages: {
            condition_detail: "请输完成条件",
        }
    });
    $step3_form.data('validator',validator);
  }

  _setValidateRule() {
  	$.validator.addMethod("arithmeticFloat",function(value,element){  
		  return /^[0-9]+(\.[0-9]?)?$/.test(value);
		}, $.validator.format("必须为正数，保留一位小数"));

    $.validator.addMethod("positiveInteger",function(value,element){  
		  return /^[1-9]\d*$/.test(value);
		}, $.validator.format("必须为正整数"));

		$.validator.addMethod("DateAndTime",function(value,element){  
			let reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
		  return reg.test(value);
		}, $.validator.format("请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm"));

  }
}

new Testpaper($('#step2-form'));