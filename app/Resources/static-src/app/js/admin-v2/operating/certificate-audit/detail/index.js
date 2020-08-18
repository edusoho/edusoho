import notify from 'common/notify';

export default class Detail {
    constructor() {
        this.init();
    }

    init() {
        // if($('.js-loading-text').length>0){
        //   $.post($('.js-loading-text').data('url'), (resp) => {
        //     $('.js-loading-text').remove();
        //     $('.js-certificate-image').html(resp);
        //   });
        // }

        // let $btn = $('#certificate-audit');
        // $btn.on('click', function (e) {
        //     let url = $btn.data('url');
        //     $btn.button('loading');
        //     $.post(url, function (data) {
        //         notify('success', Translator.trans('admin_v2.certificate.record.audit.success_hint'));
        //         window.location.reload();
        //     }).error(function () {
        //         notify('success', Translator.trans('admin_v2.certificate.record.audit.failure_hint'));
        //     });
        // });

        let $btn = $('#certificate-audit');
        $btn.on('click', function () {
            $.post($form.data('saveUrl'), $form.serialize(), function(data){
                notify('success', Translator.trans('admin_v2.certificate.record.audit.success_hint'));
                window.location.reload();
            }).error(function () {
                notify('success', Translator.trans('admin_v2.certificate.record.audit.failure_hint'));
            });
        });
    }
}

new Detail();