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

    const verifySmsCode = () => {
      const $smsCodeInput = $('#sms_code');
      const smsCode = $smsCodeInput.val().trim();
      if (smsCode === '') return false;

      let ajaxResult = false;
      $.ajax({
        url: $smsCodeInput.data('url') + '?value=' + smsCode,
        type: 'GET',
        async: false,
        success: function (response) {
          ajaxResult = !!response.success;
        }
      });

      return ajaxResult;
    };

    const doExportAjax = (verifyParams, fetchUrl, prepareUrl, finalUrl, extraParams = {}) => {
      const $modal = $('#modal');

      $.ajax({
        url: fetchUrl,
        method: 'GET',
        data: verifyParams,
        success: function (response) {
          if (!response.success) {
            window.exporting = false;
            return;
          }

          window.totalCount = response.counts.reduce((acc, val) => acc + val, 0);
          $modal.html($('#export-modal').html());
          $modal.modal({ backdrop: 'static', keyboard: false });

          const exportDataAjax = (params, start, fileName, name) => {
            params.start = start;
            params.fileName = fileName;
            params.name = name;

            $.ajax({
              url: prepareUrl,
              method: 'GET',
              data: params,
              success: function (res) {
                if (!res.success) {
                  window.exporting = false;
                  return;
                }

                if (res.status === 'finish') {
                  if (!res.csvName) {
                    $modal.modal('hide');
                    window.exporting = false;
                    return;
                  }

                  window.location.href = `${finalUrl}?fileNames[]=${res.csvName}`;
                  $modal.find('#progress-bar').width('100%').parent().removeClass('active');
                  setTimeout(() => {
                    $modal.modal('hide');
                    window.exporting = false;
                  }, 500);
                } else {
                  const progress = `${(res.start / res.count) * 100}%`;
                  $modal.find('#progress-bar').width(progress);
                  exportDataAjax(params, res.start, res.fileName, res.name);
                }
              },
              error: () => window.exporting = false
            });
          };

          exportDataAjax({ ...verifyParams, ...extraParams }, 0, '', '');
        },
        error: () => window.exporting = false
      });
    };

    $('.js-export-btn').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (self.$form.valid()) {
        console.log('验证成功，执行导出逻辑...');
      }
    });

    $('.js-export-classroom-student-btn').on('click', function (e) {
      $('[name="sms_code"]').valid();
      e.preventDefault();
      if (!verifySmsCode()) return false;
      self.exportClassroomData();
    });

    $('.js-export-course-student-btn').on('click', function (e) {
      $('[name="sms_code"]').valid();
      e.preventDefault();
      if (!verifySmsCode()) return false;
      const params = JSON.parse($('#params').val());
      doExportAjax(
        { courseSetId: params.courseSetId, courseId: params.courseId },
        '/try/export/course-students',
        '/pre/export/course-students',
        '/export/course-students'
      );
    });

    $('.item-bank-exercise-student-export').on('click', function (e) {
      $('[name="sms_code"]').valid();
      e.preventDefault();
      if (!verifySmsCode()) return false;
      const params = JSON.parse($('#params').val());
      doExportAjax(
        { exerciseId: params.exerciseId },
        '/try/export/item-bank-exercise-students',
        '/pre/export/item-bank-exercise-students',
        '/export/item-bank-exercise-students'
      );
    });

    $('.js-export-user-btn').on('click', function (e) {
      $('[name="sms_code"]').valid();
      e.preventDefault();
      const url = location.href;
      if (!verifySmsCode()) return false;

      if (url.includes('order/manage') || url.includes('admin/v2/user') || url.includes('admin/v2/staff')) {
        const smsCode = $('#sms_code').val().trim();
        const query = $('#user-search-form').serialize();
        const newUrl = `/admin/v2/users/export?sms_code=${smsCode}&mobile=${$('#mobile').val()}&${query}`;

        $.get(newUrl, function (data) {
          $('#modal').html(data);
          $('#modal').modal('show');
        });
      }
    });

    $('.js-delete-user').on('click', function (e) {
      $('[name="sms_code"]').valid();
      e.preventDefault();
      const params = JSON.parse($('#params').val());
      if (!verifySmsCode()) return false;

      $.post(params.url, function () {
        notify('success', Translator.trans('admin.user.lock_operational_success_hint', { title: params.title }));
        window.location.reload();
      }).fail(function (e) {
        const $json = $.parseJSON(e.responseText);
        const message = $json && $json.error && $json.error.message || 'admin.user.lock_operational_fail_hint';
        notify('danger', Translator.trans(message, { title: params.title }));
      });
    });
  }

  exportClassroomData(start = null, fileName = '') {
    const paramsStr = $('#params').val();
    let params;

    try {
      params = JSON.parse(paramsStr);
    } catch (e) {
      console.error('解析参数失败:', e);
      alert('参数格式错误，无法导出数据');
      return;
    }

    if (start !== null) {
      params.start = start;
    }

    if (fileName) {
      params.fileName = fileName;
    }

    const targetFormId = params['targetFormId'];
    if (!targetFormId) {
      console.error('缺少 targetFormId');
      alert('导出失败，未指定班级信息');
      return;
    }

    $.ajax({
      url: `/classroom/${targetFormId}/manage/student/export/student/datas`,
      method: 'GET',
      data: params,
      success: (response) => {
        if (response.status === 'getData') {
          // 继续导出下一部分
          this.exportClassroomData(response.start, response.fileName);
        } else if (response.fileName) {
          // 完成导出，开始下载
          window.location.href = `/classroom/${targetFormId}/manage/student/export?role=student&fileName=${encodeURIComponent(response.fileName)}`;
        } else {
          alert('导出失败，未返回文件名');
        }
      },
      error: (xhr, status, error) => {
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
    }) : null;
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