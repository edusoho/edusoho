import SmsSender from 'app/common/widget/sms-sender';
import Cookies from 'js-cookie';
import notify from 'common/notify';
import Drag from 'app/common/drag';
import Coordinate from 'app/common/coordinate';

export default class MobileBind {
  constructor() {
    this.$form = $('#mobile-bind-form');
    this.$smsCode = this.$form.find('.js-sms-send');
    this.drag = null;
    this.initDrag();
    this.dragEvent();
    this.initValidator();
    this.initMobileCodeSendBtn();
    this.initExportBtnEvent();
  }

  // +++ 新增方法：绑定导出按钮的点击事件 +++
  initExportBtnEvent() {
    const self = this;
    $('.js-export-btn').on('click', function(e) {
      e.preventDefault(); // 防止链接默认行为
      e.stopPropagation();
      if (self.$form.valid()) { // 手动触发表单验证
        // 如果验证通过，执行后续操作（如提交表单或导出）
        console.log('验证成功，执行导出逻辑...');
      }
    });

    $('.js-export-classroom-student-btn').on('click', function(e) {
      e.preventDefault();
      self.exportData();
    });

    $('.js-export-user-btn').on('click', function (e) {
      $('[name="sms_code"]').valid();
      e.preventDefault();
      const url = location.href;
      if (url.includes('order/manage') || url.includes('admin/v2/user') || url.includes('admin/v2/staff')) {
        const $smsCodeInput = $('#sms_code');
        if ($smsCodeInput.val().trim() === '') {
          return false;
        }else {
          const smsCode = $smsCodeInput.val().trim();
          let ajaxResult = false;
          $.ajax({
            url: $smsCodeInput.data('url')+'?value='+smsCode,
            type: 'GET',
            async: false,
            success: function(response) {
              console.log(response);
              console.log(response.success);
              if (response.success) {
                ajaxResult = true;
              }
            }
          });
          if (!ajaxResult) {
            return false;
          }
          const userSearchData = $('#user-search-form').serialize();
          const newUrl = '/admin/v2/users/export?sms_code='+$('#sms_code').val()+'&mobile='+$('#mobile').val()+'&'+userSearchData;
          // 先隐藏模态框
          $('#modal').modal('hide');
          // 清空原有内容（包括表单数据）
          $(this).find('#modal').empty();

          // 加载新数据
          $.get(newUrl, function(data) {
            // 填充新内容
            $('#modal').html(data);
            // 重新展示模态框
            $('#modal').modal('show');
          });
          // $.get(newUrl, function (data) {
          //   $('#modal .modal-body').html(data);
          //   $('#modal').modal('show');
          // });
        }
      }
    });
  }
  exportData() {
    var paramsStr = $('#params').val(); // 获取 JSON 字符串
    var params = JSON.parse(paramsStr); // 转成对象

    $.ajax({
      url: '/classroom/' + params['targetFormId'] + '/manage/student/export/student/datas',
      method: 'GET',
      data: params,
      success: function(response) {
        if (response.status === 'getData') {
          // 如果返回的是数据起点与文件名，递归导出或分页导出
          exportDataChunk(response.start, response.fileName);
        } else {
          // 否则直接跳转到下载链接
          window.location.href = '/classroom/' + params['targetFormId'] + '/manage/student/export?role=student&fileName=' + response.fileName;
        }
      },
      error: function(xhr, status, error) {
        console.error('导出失败：', error);
        alert('导出失败，请稍后重试');
      }
    });
  }


  dragEvent() {
    let self = this;
    if (this.drag) {
      this.drag.on('success', function(token){
        self.$smsCode.removeClass('disabled').attr('disabled', false);
      });
    }
  }

  initDrag() {
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'web_register'
    }) : null
  }

  initValidator() {
    let self = this;

    this.validator = this.$form.validate({
      currentDom: '.js-export-btn',
      ajax: true,
      rules: {
        sms_code: {
          required: true,
          unsigned_integer: true,
          es_remote: {
            type: 'get',
          },
        },
      },
      messages: {
        sms_code: {
          required: Translator.trans('auth.mobile_captcha_required_error_hint')
        }
      },
      submitSuccess(data) {
        notify('success', Translator.trans(data.message));
      },
      submitError(data) {
        notify('danger',  Translator.trans(data.responseJSON.message));
      }
    });
  }

  initMobileCodeSendBtn() {
    let self = this;

    this.$smsCode.on('click', function () {
      self.$smsCode.attr('disabled', true);
      let coordinate = new Coordinate();
      const encryptedPoint = coordinate.getCoordinate(event, $('meta[name=csrf-token]').attr('content'));
      new SmsSender({
        element: '.js-sms-send',
        url: self.$smsCode.data('url'),
        smsType: 'sms_secondary_verification',
        captcha: true,
        captchaValidated: true,
        captchaNum: 'dragCaptchaToken',
        encryptedPoint: encryptedPoint,
        preSmsSend: function() {
          return true;
        },
        error: function(error) {
          self.drag.initDragCaptcha();
        },
        additionalAction: function(ackResponse) {
          if (ackResponse == 'captchaRequired') {
            self.$smsCode.attr('disabled', true);
            $('.js-drag-jigsaw').removeClass('hidden');
            if(self.drag) {
              self.drag.initDragCaptcha();
            }
            return true;
          }
          return false;
        }
      });
    });
  }
}