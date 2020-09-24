import Cookies from 'js-cookie';
import notify from 'common/notify';

let $table = $('#information-collect-select-table');
let type = $table.data('type');
let cookieName = (type == 'course') ? 'informationCollectSelectCourseIds' : 'informationCollectSelectClassroomIds';

$('.select-target-modal').on('click', '.pagination li', (event) => {
    getCourseSets($(event.currentTarget).data('url'));
});

if (Cookies.getJSON(cookieName) && Cookies.getJSON(cookieName).length > 0) {
    getSelectedTargets($table.data('selectedUrl'));
};

$('.js-save-selected-target').on('click', (event) => {
    if ($('.has-related').length > 0) {
        notify('danger', Translator.trans('admin_v2.information_collect.chooser.tips'));
        return;
    }

    if (Cookies.getJSON(cookieName).length) {
        $('.js-action-radio-group').find('input[name="' + $(event.currentTarget).data('targetInput') + '"]').val(JSON.stringify(Cookies.getJSON(cookieName)));
    } else {
        $('.js-action-radio-group').find('input[name="' + $(event.currentTarget).data('targetInput') + '"]').val('');
    }
    $('.js-action-radio').find('.action-type-group-part .' + $(event.currentTarget).data('targetCount')).html(Cookies.getJSON(cookieName).length);
    notify('success', Translator.trans('admin_v2.information_collect.chooser.success_hint'));
    $('.select-target-modal').parent('.modal').modal('hide');
});

$('.select-target-modal').on('click', '.js-selected-item-delete', function (event) {
    let courseIds = Cookies.getJSON(cookieName);
    courseIds.splice(courseIds.indexOf($(this).parents('tr').data('id').toString()), 1);
    Cookies.set(cookieName, courseIds);

    $(this).parents('tr').remove();
    $('.js-selected-count').html(courseIds.length);
    checkItemBindedCount();
});

$('.select-target-modal').on('click', '.js-selected-item-unbind', function (e) {
    $(this).parents('.information-collect-selected-item-operate').find('.text-danger').remove();
    $(this).parents('tr').removeClass('js-selected-item-binded');
    $(this).remove();

    $('.js-selected-count').html($('.js-selected-item').length);
    checkItemBindedCount();
});

function getCourseSets(url) {
    $.get(url, { ids: Cookies.getJSON(cookieName) }, (res) => {
        $table.empty().html(res);
    });
    $('.js-selected-count').html(Cookies.getJSON(cookieName).length);
}

function getSelectedTargets(url) {
    $.get(url, { ids: Cookies.getJSON(cookieName) }, (res) => {
        $table.empty().html(res);
    });
    $('.js-selected-count').html(Cookies.getJSON(cookieName).length);
}

function checkItemBindedCount() {
    if ($('.js-selected-item-binded').length <= 0) {
        $('.has-related').remove();
    }
}
