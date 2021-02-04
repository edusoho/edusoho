define(function(require, exports, module) {
  require('jquery.bootstrap-datetimepicker');
  require('es-ckeditor');
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');

  require('jquery.select2-css');
  require('jquery.select2');

  var addStartDateRule = function () {
    Validator.addRule(
      'date_range_now',
      function (options, commit) {
        var date = Date.parse(options.element.val().replace(/-/g, '/'));
        var startTime = Date.parse(new Date());
        if (date - startTime > 0) {
          return true;
        } else {
          return false;
        }
      }, '开播时间必须大于当前时间'
    );
  };
  var addEndDateRule = function () {
    Validator.addRule(
      'end_date_range_limit',
      function (options, commit) {
        var date = Date.parse(options.element.val().replace(/-/g, '/'));
        var startTime = Date.parse($("#startDate").val().replace(/-/g, '/'));
        if (date - startTime > 0) {
          return true;
        } else {
          return false;
        }
      }, '结束时间必须大于开播时间'
    );
  };

  exports.run = function() {
    let editor = CKEDITOR.replace('liveAbout', {
      toolbar: 'Detail',
      filebrowserImageUploadUrl: $('#liveAbout').data('imageUploadUrl')
    });
    editor.on('change', () => {
      $('#liveAbout').val(editor.getData());
    });
    editor.on('blur', () => {
      $('#liveAbout').val(editor.getData());
    });

    let pageDataSets = $('#data-sets');
    let startDateInput = $("#startDate");
    let endDateInput = $("#endDate");
    let liveDuration = parseInt(pageDataSets.data('liveDuration')) * 1000;
    startDateInput.datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      endDateInput.val('');
      endDateInput.datetimepicker('setStartDate', startDateInput.val().substring(0, 16));
      endDateInput.datetimepicker('setEndDate', new Date(Date.parse(new Date(startDateInput.val().substring(0, 16))) + liveDuration));
      startDateInput.focus();
    });
    startDateInput.datetimepicker('setStartDate', new Date(Date.parse(new Date())));

    endDateInput.datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      endDateInput.focus();
    });
    endDateInput.datetimepicker('setStartDate', startDateInput.val().substring(0, 16));
    endDateInput.datetimepicker('setEndDate', new Date(Date.parse(new Date(startDateInput.val().substring(0, 16))) + liveDuration));

    var enrollSmsSwitchBtnVal = $('#enrollSms');
    var enrollSmsSwitchBtn = $('[data-toggle="enrollSmsSwitch"]');
    if (1 !== parseInt(enrollSmsSwitchBtnVal.val())) {
      enrollSmsSwitchBtn.parent().removeClass('checked');
    }
    enrollSmsSwitchBtn.on('click', function() {
      enrollSmsSwitchBtnVal.val() == 1 ? enrollSmsSwitchBtnVal.val(0) : enrollSmsSwitchBtnVal.val(1);
      switchCheck(enrollSmsSwitchBtn);
    });

    var enrollWechatSwitchBtnVal = $('#enrollWechat');
    var enrollWechatSwitchBtn = $('[data-toggle="enrollWechatSwitch"]');
    if (1 !== parseInt(enrollWechatSwitchBtnVal.val())) {
      enrollWechatSwitchBtn.parent().removeClass('checked');
    }
    enrollWechatSwitchBtn.on('click', function() {
      enrollWechatSwitchBtnVal.val() == 1 ? enrollWechatSwitchBtnVal.val(0) : enrollWechatSwitchBtnVal.val(1);
      switchCheck(enrollWechatSwitchBtn);
    });

    let switchCheck = function(target) {
      let $this = $(target);
      let $parent = $this.parent();

      if ($parent.hasClass('checked')) {
        $parent.removeClass('checked');
      } else {
        $parent.addClass('checked');
      }
    };
    let speakerContainer = $('#speaker');
    speakerContainer.select2({
      ajax: {
        url: speakerContainer.data('url') + '#',
        dataType: 'json',
        quietMillis: 100,
        data: function (term, page) {
          return {
            q: term,
          };
        },
        results: function (data) {
          var results = [];
          $.each(data, function (index, item) {
            results.push({
              id: item.id,
              name: item.name,
              avatar: item.avatar,
              mobile: item.mobile,
              email: item.email
            });
          });

          return {
            results: results
          };
        }
      },
      initSelection: function (element, callback) {
        let initSpeaker = element.data('speaker');
        if ('' === initSpeaker) {
          return;
        }
        let speakerData = {
          id: initSpeaker.id,
          name: initSpeaker.name
        };
        callback(speakerData);
      },
      formatSelection: function (item) {
        $('#realSpeaker').val(item.id);
        return item.name;
      },
      formatResult: function (item) {
        return '<div class="col-md-2"><img src="'+ item.avatar +'" style="width: auto;height: 45px" class="cd-avatar cd-avatar-xs" /></div><div class="col-md-8">' + item.name + '<span class="color-mobile ml8">'+item.mobile+'</span>' + '<p>'+item.email+'</p></div>';
      },
      width: 'off',
      maximumSelectionSize: 50,
      maximumInputLength: 10,
      placeholder: Translator.trans('请输入讲师信息'),
      tokenSeparators: [",", " "]
    });

    addStartDateRule();
    addEndDateRule();
    let validator = new Validator({
      element: '#open-live-form',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        $('#save-open-live').button('submiting').addClass('disabled');
        $.post($form.data('saveUrl'), $form.serialize(), function(res) {
          if (res.success) {
            Notify.success(Translator.trans(pageDataSets.data('pageActionTitle') + '公开课成功'));
            window.location.href = $form.data('successUrl');
            return;
          }
          let errorMsg = pageDataSets.data('pageActionTitle') + '公开课失败';
          if (typeof res.errorMsg !== 'undefined') {
            errorMsg = res.errorMsg;
          }
          Notify.danger(Translator.trans(errorMsg));
          restSubmitBtn($('#save-open-live'));
        }).error(function(){
          Notify.danger(Translator.trans('服务出错，操作失败！'));
          restSubmitBtn($('#save-open-live'));
        });
      }
    });

    validator.addItem({
      element: '[name="title"]',
      required: true,
      rule: 'maxlength{max:200}'
    });

    validator.addItem({
      element: '[name="startDate"]',
      required: true,
      rule: 'date_range_now',
      errormessageRequired: Translator.trans('course.manage.expiry_start_date_error_hint'),
    });

    validator.addItem({
      element: '[name="endDate"]',
      required: true,
      rule: 'end_date_range_limit',
      errormessageRequired: Translator.trans('course.manage.expiry_end_date_error_hint'),
    });

    validator.addItem({
      element: '[name="realSpeaker"]',
      required: true,
      errormessageRequired: Translator.trans('请选择讲师'),
    });

    validator.addItem({
      element: '[name="openLiveCover"]',
      required: true,
      errormessageRequired: Translator.trans('请上传封面图'),
    });

    function restSubmitBtn(btn) {
      btn.html('提交');
      btn.removeClass('disabled');
    }
  }
});