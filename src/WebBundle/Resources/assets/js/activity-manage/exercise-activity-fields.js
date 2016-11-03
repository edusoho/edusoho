class Exercise {
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
    var  $step2_form = $("#step2-form");
    var validator = $step2_form.validate({
        onkeyup: false,
        rules: {
            title: {
              required:true,
              maxlength:30
            },
            itemCount: {
              required: true,
              positiveInteger:true
            },
            range:{
            	required:true,
            },
            difficulty:{
            	required:true
            },
            'questionTypes[]':{
              required:true
            }
        },
        messages: {
            title: {
              required: "请输入标题",
              maxlength: "标题不要超过30个字符"
            },
            range: "题目来源",
            difficulty: "请选择难易程度",
            'questionTypes[]': "请选择题型"
        }
    });

    /*if (validator.form()) {
      $('input[namae="checkQuestion"]').rules('add',{
        required:true,
        remote:{
          url: $('input[namae="checkQuestion"]').data('checkUrl'),     
          type: 'post',               
          dataType: 'json',           
          data: $step2_form.serialize(),
          success:function(response){
            console.log(response);
          }
        }
      })
    }*/

    $step2_form.data('validator',validator);
  }

  _inItStep3form() {
    var $step3_form = $("#step3-form");
    var validator = $step3_form.validate({
        onkeyup: false,
        rules: {
            finishCondition: {
                required: true,
            },
        },
        messages: {
            finishCondition: "请输完成条件",
        }
    });
    $step3_form.data('validator',validator);
  }

  _setValidateRule() {
    $.validator.addMethod("positiveInteger",function(value,element){  
		  return this.optional( element ) || /^[1-9]\d*$/.test(value);
		}, $.validator.format("必须为正整数"));

  }
}

new Exercise($('#step2-form'));