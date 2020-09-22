import notify from 'common/notify';
import './localtion';

let $form = $('#information-collection-form');
let validator = $form.validate({
    rules: {
        title: {
            required: true,
            byte_maxlength: 100
        },
        action: {
            required: true
        },
        allowSkip: {
            required: true,
        },
        status: {
            required: true
        },
        formTitle: {
            required: true,
            byte_maxlength: 30
        }
    }
});

$('.js-save-btn').on('click', (event) => {
    if (validator && validator.form()) {
        console.log($form.serialize());
    }
    // $.post(
    //     $form.data('url'), $form.serialize()
    // ).success(function (response) {
    //     notify('success', Translator.trans('site.save_success_hint'));
    // }).fail(function (xhr, status, error) {
    //     notify('danger', xhr.responseJSON.error.message);
    // });
});


if ($('input[name="action"]').length) {
    $('input[name="action"]').on('click', (event) => {
        let $group = $(event.currentTarget).parent().find('.checkbox-group');
        $(event.currentTarget).parents('.action-radio').siblings('.action-radio').find('.checkbox-group').html('');

        if (!$group.find('.radios').length) {
            $group.html($('.radio-for-action').html());
        }
    });
}
