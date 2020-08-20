import notify from 'common/notify';

export default class Detail {
  constructor() {
    this.init();
  }

  init() {
    let $form = $("#audit-form");
    let $btn = $('#certificate-audit');
    $btn.on('click', function () {
      $.post($form.data('saveUrl'), $form.serialize(), function (data) {
        notify('success', Translator.trans('admin_v2.certificate.record.audit.success_hint'));
        // window.location.reload();
      }).error(function () {
        notify('success', Translator.trans('admin_v2.certificate.record.audit.failure_hint'));
      });
    });

    let $inputAuditPass = $("#audit-pass");
    let $inputAuditReject = $("#audit-reject");
    let $inputAuditTodo = $("#audit-todo");
    let $inputRejectReason = $("#reject-reason");
    $inputAuditReject.on('click', function () {
      $inputRejectReason.show();
    });
    $inputAuditPass.on('click', function () {
      $inputRejectReason.hide();
    });
    $inputAuditTodo.on('click', function () {
      $inputRejectReason.hide();
    });
  }
}

new Detail();