class Homework
{
	constructor($form) {
		this.$element = $form;
		this._setValidateRule();
    this._init();
    this._initEvent();
    this.$parentIframe = $(window.parent.document).find('#task-manage-content-iframe');
  }

  _init() {
  	/*var editor = CKEDITOR.replace('homework-about-field', {
      toolbar: 'Minimal',
      filebrowserImageUploadUrl: $('#homework-about-field').data('imageUploadUrl')
    });*/

    this._inItStep2form();
  }

  _initEvent() {
  	this.$element.find('[data-role="pick-item"]').on('click', event=>this._showPickQuestion(event));
  }

  _showPickQuestion(event) {
  	event.preventDefault();
  	let $btn = $(event.currentTarget);

    let excludeIds = [];

    $('#course-tasks-next',window.parent.document).attr("disabled", false);

    $("#homework-table-tbody").find('[name="questionId[]"]').each(function() {
        excludeIds.push($(this).val());
    });

    let $modal = $("#attachment-modal",window.parent.document).modal();
    $modal.data('manager', this);

    $.get($btn.data('url'), {
        excludeIds: excludeIds.join(',')
    }, function(html) {
        $modal.html(html);
    });
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

new Homework($('#step2-form'));