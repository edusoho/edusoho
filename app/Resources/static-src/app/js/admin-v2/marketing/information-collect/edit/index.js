import notify from 'common/notify';
import Cookies from 'js-cookie';
import './location';

clearInformationCollectCookies();
let $form = $('#information-collect-form');
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
        },
        selectedTargetTypes: {
            checkoutTargetTypes: true,
        },
        courseIds: {
            checkSelectedCourseIds: true
        },
        classroomIds: {
            checkSelectedClassroomIds: true
        },
    },
});

$.validator.addMethod('checkoutTargetTypes', function (value, element) {
    if ($('input[name="locationType"]:checked').val() == 'all') {
        return true;
    }

    if ($('input[name="locationType"]:checked').val() == 'part') {
        let $checkedTargetTypes = $('.targetTypes:checked');
        if (!$checkedTargetTypes.length) {
            return false;
        }
    }

    return true;
}, $.validator.format(Translator.trans('admin_v2.information_collect.chooser.target_hint')));

$.validator.addMethod('checkSelectedCourseIds', function (value, element) {
    if ($('input[name="locationType"]:checked').val() == 'all') {
        return true;
    }
    let result = true;

    if ($('input[name="locationType"]:checked').val() == 'part') {
        let $checkedTargetTypes = $('.targetTypes:checked');

        $.each($checkedTargetTypes, function (index, value) {
            if ($(value).val() == 'course') {
                result = $('input[name="courseIds"]').val().length > 0;
                return;
            }
        });
    }

    return result;
}, $.validator.format(Translator.trans('admin_v2.information_collect.chooser.target_course_hint')));

$.validator.addMethod('checkSelectedClassroomIds', function (value, element) {
    if ($('input[name="locationType"]:checked').val() == 'all') {
        return true;
    }
    let result = true;

    if ($('input[name="locationType"]:checked').val() == 'part') {
        let $checkedTargetTypes = $('.targetTypes:checked');
        $.each($checkedTargetTypes, function (index, value) {
            if ($(value).val() == 'classroom') {
                result = $('input[name="classroomIds"]').val().length > 0;
                return;
            }
        });
    }

    return result;
}, $.validator.format(Translator.trans('admin_v2.information_collect.chooser.target_classroom_hint')));

$('.js-save-btn').on('click', (event) => {
    if (validator && validator.form()) {
        $.post(
            $form.data('url'), getFormData()
        ).success(function (response) {
            notify('success', Translator.trans('site.save_success_hint'));
            window.location.href = $(event.currentTarget).data('redirectUrl');
        }).fail(function (xhr, status, error) {
            notify('danger', xhr.responseJSON.error.message);
        });
    }
});

function getFormData() {
    let data = {};

    $.each($form.serializeArray(), function (index, value) {
        data[value.name] = value.value;
    });

    if (data.locationType == 'all') {
        data.courseIds = JSON.stringify(['0']);
        data.classroomIds = JSON.stringify(['0']);
    } else if (data.locationType == 'part') {
        data.courseIds = $('input[name="locationType"]:checked').parents('.js-action-radio-group').find('input[name="courseIds"]').val();
        data.classroomIds = $('input[name="locationType"]:checked').parents('.js-action-radio-group').find('input[name="classroomIds"]').val();
    }

    return data;
}

if ($('input[name="action"]').length) {
    $('input[name="action"]').on('click', (event) => {
        let $group = $(event.currentTarget).parent().find('.js-checkbox-group');
        $(event.currentTarget).parents('.js-action-radio').siblings('.js-action-radio').find('.js-checkbox-group').html('');

        if (!$group.find('.radios').length) {
            $group.html($('.radio-for-action').html());
        }

        clearInformationCollectCookies();
    });
}

function clearInformationCollectCookies() {
    if (Cookies.getJSON('informationCollectSelectCourseIds')) {
        Cookies.set('informationCollectSelectCourseIds', []);
    }

    if (Cookies.getJSON('informationCollectSelectClassroomIds')) {
        Cookies.set('informationCollectSelectClassroomIds', []);
    }
}
