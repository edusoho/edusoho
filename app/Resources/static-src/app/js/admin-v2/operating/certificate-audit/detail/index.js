import notify from 'common/notify';

export default class Detail {
    constructor() {
        this.init();
    }

    init() {
        let $form = $("#audit-form");
        let $btn = $('#certificate-audit');
        $btn.on('click', function () {
            $.post($form.data('saveUrl'), $form.serialize(), function(data){
                notify('success', Translator.trans('admin_v2.certificate.record.audit.success_hint'));
                // window.location.reload();
            }).error(function () {
                notify('success', Translator.trans('admin_v2.certificate.record.audit.failure_hint'));
            });
        });

        let $btnAuditPass = $("#audit-pass");
        let $btnAuditReject = $("#audit-reject");
        let $btnAuditTodo = $("#audit-todo");
        let $inputRejectReason = $("#reject-reason");
        function rejectReason() {
            $btnAuditReject.is(':checked');
            $inputRejectReason.show();
        }
        rejectReason();
        // $btnAuditReject.on('check', function(){
        //     $btnAuditReject.show()
        // })
    }
}

new Detail();