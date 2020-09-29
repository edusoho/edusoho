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
    messages: {
        items: {
            required: Translator.trans('admin_v2.information_collect.chooser.items.hint')
        }
    }
});

$.validator.addMethod('checkoutTargetTypes', function (value, element) {
    if (typeof($("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').val()) == 'undefined') {
        return false;
    }

    if ($("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').val() == 'all') {
        let $checkedTargetTypes = $("[name='action']:checked").parents('.js-action-radio').find('.target-types-all:checked');
        if (!$checkedTargetTypes.length) {
            return false;
        }
    }

    if ($("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').val() == 'part') {
        let $checkedTargetTypes = $("[name='action']:checked").parents('.js-action-radio').find('.target-types-part:checked');
        if (!$checkedTargetTypes.length) {
            return false;
        }
    }

    return true;
}, $.validator.format(Translator.trans('admin_v2.information_collect.chooser.target_hint')));

$.validator.addMethod('checkSelectedCourseIds', function (value, element) {
    if ($("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').val() == 'all') {
        return true;
    }

    let $checkedTargetCourse = $("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').parents('.action-type-group').find('.target-course:checked');
    return !$checkedTargetCourse.length || ($checkedTargetCourse.length && store.get('information_collect_selected_' + $("[name='action']:checked").val() + '_course_ids').length > 0);
}, $.validator.format(Translator.trans('admin_v2.information_collect.chooser.target_course_hint')));

$.validator.addMethod('checkSelectedClassroomIds', function (value, element) {
    if ($("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').val() == 'all') {
        return true;
    }
    
    let $checkedTargetClassroom = $("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').parents('.action-type-group').find('.target-classroom:checked');
    return !$checkedTargetClassroom.length || ($checkedTargetClassroom.length && store.get('information_collect_selected_' + $("[name='action']:checked").val() + '_classroom_ids').length > 0);
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

    if ($("[name='action']:checked").parents('.js-action-radio').find('.js-location-type:checked').val() == 'all') {
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
        data.courseIds = store.get('information_collect_selected_'+data.action+'_course_ids');
        data.classroomIds = store.get('information_collect_selected_' + data.action + '_classroom_ids');
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
            $('.allow-skip-help-block').hasClass('hidden') ? $('.allow-skip-help-block').removeClass('hidden') : '';
            $('input[name="allowSkip"]').eq('0').prop('checked', true);
            $('input[name="allowSkip"]').eq('1').prop('disabled', true);
        } else {
            $('.allow-skip-help-block').hasClass('hidden') ? '' : $('.allow-skip-help-block').addClass('hidden');
            $('input[name="allowSkip"]').eq('1').removeProp('disabled');
            $('input[name="allowSkip"]').eq('1').prop('checked', true);
        }
    });
}

function clearInformationCollectStorage() {
    $.each($('input[name="action"]'), function (index, action) {
        $.each($('input[name="targetTypes[]"]'), function (index, type) {
            let actionName = $(action).val();
            let typeName = $(type).val();
            store.get('information_collect_' + actionName + '_' + typeName + '_ids', []) ? store.set('information_collect_' + actionName + '_' + typeName + '_ids', []) : '';
            store.get('information_collect_selected_' + actionName + '_' + typeName + '_ids', []) ? store.set('information_collect_selected_' + actionName + '_' + typeName + '_ids', []) : '';
            if ($('input[name="' + actionName + '_' + typeName + '_ids"]').val()) {
                store.set('information_collect_' + actionName + '_' + typeName + '_ids', JSON.parse($('input[name="' + actionName  + '_' + typeName + '_ids"]').val()));
                store.set('information_collect_selected_' + actionName + '_' + typeName + '_ids', JSON.parse($('input[name="' + actionName  + '_' + typeName + '_ids"]').val()));
            }            
        });
    });
}
