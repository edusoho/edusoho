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
    let editor = CKEDITOR.replace('homework-about-field', {
        toolbar: 'Minimal',
        filebrowserImageUploadUrl: $('#homework-about-field').data('imageUploadUrl'),
    });

    this._inItStep2form();
  }

  _initEvent() {
  	this.$element.on('click', '[data-role="pick-item"]',event=>this._showPickQuestion(event));
  }

  _showPickQuestion(event) {
  	event.preventDefault();
  	let $btn = $(event.currentTarget);

    let excludeIds = [];

    $('#course-tasks-next',window.parent.document).attr("disabled", false);

    $("#question-table-tbody").find('[name="questionIds[]"]').each(function() {
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
        title:{
          required:true
        },
        'questionId[]':{
          required:true
        }
      },
      messages: {
        title:"请填写标题",
        'questionId[]':{
          required:"请选择题目"
        }
      },
      
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

class QuestionPicked
{
  constructor($form){
    this.$form = $form;
    this._init();
    this._initEvent();
  }

  _init()
  {

  }

  _initEvent()
  {
    this.$form.on('click', '.item-delete-btn',event=>this._deleteItemQuestion(event));
    this.$form.on('click', '.question-preview', event=>this._questionPreview(event));
  }

  _deleteItemQuestion(event)
  {
    let $btn = $(event.currentTarget);
    if (!confirm('您真的要删除该题目吗？')) {
        return;
    }
    var $tr = $btn.parents('tr');
    //$tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
    $tr.remove();
    this._refreshSeqs();
    this._refreshPassedDivShow();
  }

  _refreshSeqs()
  {
    let seq = 1;
    this.$form.find("tbody tr").each(function() {
      let $tr = $(this);
      $tr.find('td.seq').html(seq);
      seq++;
    });
  }

  _refreshPassedDivShow()
  {
    var hasEssay = false;
    this.$form.find("tbody tr").each(function() {
      if ($(this).data('type') == 'essay' || $(this).data('type') == 'material') {
        hasEssay = true;
      }
    });

    if (hasEssay) {
        $(".correctPercentDiv").html('');
    } else {
        var html = '这是一份纯客观题的作业，正确率达到为' +
            '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent1" value="60" />％合格，' +
            '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent2" value="80" />％良好，' +
            '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent3" value="100" />％优秀';

        $(".correctPercentDiv").html(html);
    }
  }

  _questionPreview(event)
  {
    window.open($(event.currentTarget).data('url'), '_blank',
                "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
  }
}


new Homework($('#step2-form'));
new QuestionPicked($('#step2-form'));

