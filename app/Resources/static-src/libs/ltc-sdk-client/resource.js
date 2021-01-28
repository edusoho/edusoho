let validate = function() {
  $.extend($.validator.messages, {
    required: '请输入%display%',
    remote: '请修正此字段',
    email: '请输入有效的电子邮件地址',
    url: '请输入有效的网址',
    date: '请输入有效的日期',
    dateISO: '请输入有效的日期 (YYYY-MM-DD)',
    number: '请输入有效的数字',
    digits: '只能输入整数',
    equalTo: '你的输入不相同',
    maxlength: '最多只能输入 {0} 个字符',
    minlength: '最少需要输入 {0} 个字符',
    rangelength: '请输入长度在 {0} 到 {1} 之间的字符串',
    range: '请输入范围在 {0} 到 {1} 之间的数值',
    max: '请输入不大于 {0} 的数值',
    min: '请输入不小于 {0} 的数值'
  });

  $.validator.setDefaults({
    errorClass: 'form-error-message help-block jq-validate-error',
    errorElement: 'p',
    onkeyup: false,
    ignore: '',
    ajax: false,
    currentDom: null,
    highlight: function(element, errorClass, validClass) {
      let $row = $(element).addClass('form-control-error').closest('.form-group').addClass('has-error');
      $row.find('.help-block').hide();
    },
    unhighlight: function(element, errorClass, validClass) {
      let $row = $(element).removeClass('form-control-error').closest('.form-group');
      $row.removeClass('has-error');
      $row.find('.help-block').show();
    },
    errorPlacement: function(error, element) {
      if (element.parent().hasClass('controls')) {
        element.parent('.controls').append(error);
      } else if (element.parent().hasClass('input-group')) {
        element.parent().after(error);
      } else if (element.parent().is('label')) {
        element.parent().parent().append(error);
      } else {
        element.parent().append(error);
      }
    },
    invalidHandler: function(data) {
      console.log(data);
    },
    submitError: function(data) {
      console.log('submitError');
    },
    submitSuccess: function(data) {
      console.log('submitSuccess');
    },
    submitHandler: function(form) {
    }
  });

  $.validator.addMethod('trim', function(value, element, params) {
    return this.optional(element) || $.trim(value).length > 0;
  }, '请输入%display%');
  
  $.validator.addMethod('course_title', function(value, element, params) {
    return this.optional(element) || /^[^<>]*$/.test(value);
  }, '不支持输入<、>字符');

  $.extend($.validator.prototype, {
    defaultMessage: function(element, rule) {
      if (typeof rule === 'string') {
        rule = { method: rule };
      }
  
      var message = this.findDefined(
          this.customMessage(element.name, rule.method),
          this.customDataMessage(element, rule.method),
  
          // 'title' is never undefined, so handle empty string as undefined
          !this.settings.ignoreTitle && element.title || undefined,
          $.validator.messages[rule.method],
          '<strong>Warning: No message defined for ' + element.name + '</strong>'
        ),
        theregex = /\$?\{(\d+)\}/g,
        displayregex = /%display%/g;
      if (typeof message === 'function') {
        message = message.call(this, rule.parameters, element);
      } else if (theregex.test(message)) {
        message = $.validator.format(message.replace(theregex, '{$1}'), rule.parameters);
      }
  
      if (displayregex.test(message)) {
        var labeltext, name;
        var id = $(element).attr('id') || $(element).attr('name');
        if (id) {
          labeltext = $('label[for=' + id + ']').text();
          if (labeltext) {
            labeltext = labeltext.replace(/^[\*\s\:\：]*/, '').replace(/[\*\s\:\：]*$/, '');
          }
        }
  
        name = $(element).data('display') || $(element).attr('name');
        message = message.replace(displayregex, labeltext || name);
      }
  
      return message;
    }
  
  });  
}


let jquery = () => {
  $.fn.serializeObject = function()
  {
    let o = {};
    let a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };

  $(document).ajaxSend(function (a, b, c) {
    if (c.type === 'POST') {
      b.setRequestHeader('X-CSRF-Token', window.ltc.getContext().csrf);
    }
  });
};

let editor = () => {
  window.ltc.editor = (value) => {
    $(`#${value}`).data('imageDownloadUrl', window.ltc.getEditorConfig()['imageDownloadUrl'])
    
    return CKEDITOR.replace(value, Object.assign({
      toolbar: 'Task',
      fileSingleSizeLimit: 2,
      allowedContent: true,
      height: 300,
    }, window.ltc.getEditorConfig()));
  }
}


export {validate,  jquery, editor}