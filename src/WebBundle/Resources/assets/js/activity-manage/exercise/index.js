class Exercise {
	constructor($form) {
		this.$element = $form;
		this._setValidateRule();
    this._init();
    this._initEvent();
  }

  _init() {
    this._inItStep2form();
  }

  _initEvent() {
  	
  }

  _inItStep2form() {
    var  $step2_form = $("#step2-form");
    var validator = $step2_form.validate({
        onkeyup: false,
        rules: {
          title: {
            required:true
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
            required:true,
            remote: {    
              url: $('[name="checkQuestion"]').data('checkUrl'),   
              type: "post",    
              dataType: "json",       
              data: {    
                itemCount: function() {    
                  return $('[name="itemCount"]').val();    
                },
                range: function() {
                  return $('[name="range"]:checked').val();
                },
                difficulty: function() {
                  return $('[name="difficulty"]').val();
                },
                types: function () {
                  let types = [];
                  $('[name="questionTypes\[\]"]:checked').each(function(){
                    types.push($(this).val());
                  });
                  return types;
                }
              }    
            }    
          }
        },
        messages: {
          required:"请填写标题",
          range: "题目来源",
          difficulty: "请选择难易程度",
          'questionTypes[]': {
            required:"请选择题型",
            remote:"题目数量不足"
          },
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