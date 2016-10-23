import 'jquery-validation';

$.validator.setDefaults({
  errorClass: 'help-block jq-validate-error',
  errorElement: 'p',
  highlight: function(element, errorClass, validClass) {
    let $row = $(element).parents('.form-group');
    $row.addClass('has-error');
    $row.find('.help-block').each(function() {
      let $this = $(this);
      if (!$this.hasClass('jq-validate-error')) {
        $this.hide();
      }
    });
  },
  unhighlight: function(element, errorClass, validClass) {
    let $row = $(element).parents('.form-group');
    $row.removeClass('has-error');
    $row.find('.help-block').each(function() {
      let $this = $(this);
      if (!$this.hasClass('jq-validate-error')) {
        $this.show();
      }
    });
  },
  errorPlacement: function(error, element) {
    if (element.parent().hasClass('input-group')) {
      element.parent().after(error);
    } else if (element.parent().is('label')) {
      element.parent().after(error);
    } else {
      element.after(error);
    }
  },
  submitHandler: function(form) {
    let $submitBtn = $(form).find('[type="submit"][data-loading-text]');
    $submitBtn.attr('disabled', 'disabled');
    $submitBtn.text($submitBtn.data('loadingText'));
    form.submit();
  }
});

$.extend($.validator.messages, {
  required: "这是必填字段",
  remote: "请修正此字段",
  email: "请输入有效的电子邮件地址",
  url: "请输入有效的网址",
  date: "请输入有效的日期",
  dateISO: "请输入有效的日期 (YYYY-MM-DD)",
  number: "请输入有效的数字",
  digits: "只能输入数字",
  creditcard: "请输入有效的信用卡号码",
  equalTo: "你的输入不相同",
  extension: "请输入有效的后缀",
  maxlength: $.validator.format( "最多可以输入 {0} 个字符" ),
  minlength: $.validator.format( "最少要输入 {0} 个字符" ),
  rangelength: $.validator.format( "请输入长度在 {0} 到 {1} 之间的字符串" ),
  range: $.validator.format( "请输入范围在 {0} 到 {1} 之间的数值" ),
  max: $.validator.format( "请输入不大于 {0} 的数值" ),
  min: $.validator.format( "请输入不小于 {0} 的数值" )
});

$.validator.addMethod("idcardNumber", function(value, element, params) {
  let _check = function(idcardNumber) {
    let reg = /^\d{17}[0-9xX]$/i;
    if (!reg.test(idcardNumber)) {
      return false;
    }
    let n = new Date();
    let y = n.getFullYear();
    if (parseInt(idcardNumber.substr(6, 4)) < 1900 || parseInt(idcardNumber.substr(6, 4)) > y) {
      return false;
    }
    let birth = idcardNumber.substr(6, 4) + "-" + idcardNumber.substr(10, 2) + "-" + idcardNumber.substr(12, 2);
    if (!'undefined' == typeof birth.getDate) {
      return false;
    }
    let iW = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);
    let iSum = 0;
    for (let i = 0; i < 17; i++) {
      iSum += parseInt(idcardNumber.charAt(i)) * iW[i];
    }
    let iJYM = iSum % 11;
    let sJYM = ''
    if (iJYM == 0) sJYM = '1';
    else if (iJYM == 1) sJYM = '0';
    else if (iJYM == 2) sJYM = 'x';
    else if (iJYM == 3) sJYM = '9';
    else if (iJYM == 4) sJYM = '8';
    else if (iJYM == 5) sJYM = '7';
    else if (iJYM == 6) sJYM = '6';
    else if (iJYM == 7) sJYM = '5';
    else if (iJYM == 8) sJYM = '4';
    else if (iJYM == 9) sJYM = '3';
    else if (iJYM == 10) sJYM = '2';
    let cCheck = idcardNumber.charAt(17).toLowerCase();
    if (cCheck != sJYM) {
      return false;
    }
    return true;
  }
  return this.optional(element) || _check(value);
}, "请正确输入您的身份证号码");
