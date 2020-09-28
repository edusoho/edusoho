import notify from 'common/notify';
import 'store';
import './location';
import './form'

clearInformationCollectStorage();
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
        items: {
            required: true
        }
    },
});

$.validator.addMethod('checkoutTargetTypes', function (value, element) {
    if ($('input[name="locationType"]:checked').val() == 'all') {
        let $checkedTargetTypes = $('input[name="locationType"]:checked').parents('.action-type-group').find('.target-types-all:checked');
        if (!$checkedTargetTypes.length) {
            return false;
        }
    }

    if ($('input[name="locationType"]:checked').val() == 'part') {
        let $checkedTargetTypes = $('input[name="locationType"]:checked').parents('.action-type-group').find('.target-types-part:checked');
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

    let $checkedTargetCourse = $('input[name="locationType"]:checked').parents('.action-type-group').find('.target-course:checked');
    return !$checkedTargetCourse.length || ($checkedTargetCourse.length && $('input[name="courseIds"]').val().length > 0);
}, $.validator.format(Translator.trans('admin_v2.information_collect.chooser.target_course_hint')));

$.validator.addMethod('checkSelectedClassroomIds', function (value, element) {
    if ($('input[name="locationType"]:checked').val() == 'all') {
        return true;
    }
    let $checkedTargetClassroom = $('input[name="locationType"]:checked').parents('.action-type-group').find('.target-classroom:checked');
    return !$checkedTargetClassroom.length || ($checkedTargetClassroom.length && $('input[name="classroomIds"]').val().length > 0);
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
        if (value.name != 'targetTypes[]') {
            data[value.name] = value.value;
        }
    });

    if (data.locationType == 'all') {
        data.targetTypes = [
            $('.js-checkbox-group .action-type-group-all').find('.target-course:checked').length ? 'course' : null,
            $('.js-checkbox-group .action-type-group-all').find('.target-classroom:checked').length ? 'classroom' : null,
        ];
        data.courseIds = JSON.stringify(['0']);
        data.classroomIds = JSON.stringify(['0']);
    } else {
        data.targetTypes = [
            $('.js-checkbox-group .action-type-group-part').find('.target-course:checked').length ? 'course' : null,
            $('.js-checkbox-group .action-type-group-part').find('.target-classroom:checked').length ? 'classroom' : null,
        ];
        data.courseIds = $('.js-checkbox-group .action-type-group-part').find('.target-course:checked').length ? $('input[name="courseIds"]').val() : [];
        data.classroomIds = $('.js-checkbox-group .action-type-group-part').find('.target-classroom:checked').length ? $('input[name="classroomIds"]').val() : [];
    }

    data.items = [];

    let i = 1;
    $.each($('.list-group-item'), function (index, value) {
        if ($(value).data('selected')) {
            data.items.push({
                code: $(value).data('code'),
                labelName: $(value).data('labelName'),
                required: $(value).data('required') ? 1 : 0,
                seq: i
            });
            i++;
        }
    });

    return data;
}

if ($('input[name="action"]').length) {
    $('input[name="action"]').on('click', (event) => {
        let $group = $(event.currentTarget).parents('.js-action-radio').find('.js-checkbox-group');
        let $siblingGroup = $(event.currentTarget).parents('.js-action-radio').siblings('.js-action-radio').find('.js-checkbox-group');

        $group.hasClass('hidden') ? $group.removeClass('hidden') : '';
        $siblingGroup.hasClass('hidden') ? '' : $siblingGroup.addClass('hidden');

        if ($(event.currentTarget).val() == 'buy_after') {
            $('input[name="allowSkip"]').eq('0').prop('checked', true);
            $('input[name="allowSkip"]').eq('1').prop('disabled', true);
        } else {
            $('input[name="allowSkip"]').eq('1').removeProp('disabled', true);
            $('input[name="allowSkip"]').eq('1').prop('checked', true);
            $('input[name="allowSkip"]').eq('0').prop('disabled', true);
        }

        clearInformationCollectStorage();
    });
}

function clearInformationCollectStorage() {
    $.each($('input[name="action"]'), function (index, action) {
        $.each($('input[name="targetTypes[]"]'), function (index, type) {
            store.get('information_collect_' + $(action).val() + '_' + $(type).val() + '_ids', []).length ? store.set('information_collect_' + $(action).val() + '_' + $(type).val() + '_ids', []) : '';
            store.get('information_collect_selected_' + $(action).val() + '_' + $(type).val() + '_ids', []).length ? store.set('information_collect_selected_' + $(action).val() + '_' + $(type).val() + '_ids', []) : '';
        });
    });
}
