import 'store';
import notify from 'common/notify';

let $table = $('#information-collect-select-table');
let type = $table.data('type');
let storeName = (type == 'course') ? 'informationCollectSelectCourseIds' : 'informationCollectSelectClassroomIds';
let selectedStoreName = (type == 'course') ? 'informationCollectSelectedCourseIds' : 'informationCollectSelectedClassroomIds';

$('.select-target-modal').on('click', '.pagination li', (event) => {
    getCourseSets($(event.currentTarget).data('url'));
});

if (!store.get((storeName, []).length) && $('input[name="' + type + 'Ids"]').val()) {
    store.set(storeName, JSON.parse($('input[name="' + type + 'Ids"]').val()));
}

if (store.get(storeName, []).length > 0) {
    getSelectedTargets($table.data('selectedUrl'));
};

$('.js-save-selected-target').on('click', (event) => {
    if ($('.has-related').length > 0) {
        notify('danger', Translator.trans('admin_v2.information_collect.chooser.tips'));
        return;
    }

    store.set(selectedStoreName, store.get(storeName, []));

    if (store.get(storeName, []).length) {
        $('.js-action-radio-group').find('input[name="' + $(event.currentTarget).data('targetInput') + '"]').val(JSON.stringify(store.get(storeName)));
    } else {
        $('.js-action-radio-group').find('input[name="' + $(event.currentTarget).data('targetInput') + '"]').val('');
    }
    $('.js-action-radio').find('.action-type-group-part .' + $(event.currentTarget).data('targetCount')).html(store.get(storeName).length);
    notify('success', Translator.trans('admin_v2.information_collect.chooser.success_hint'));
    $('.select-target-modal').parent('.modal').modal('hide');
});

$('.select-target-modal').on('click', '.js-selected-item-delete', function (event) {
    let courseIds = store.get(storeName, []);
    courseIds.splice(courseIds.indexOf($(this).parents('tr').data('id').toString()), 1);
    store.set(storeName, courseIds);

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
    $.get(url, { ids: store.get(storeName, []) }, (res) => {
        $table.empty().html(res);
    });
    $('.js-selected-count').html(store.get(storeName, []).length);
}

function getSelectedTargets(url) {
    $.get(url, { 'action': $("[name='action']:checked").val(), ids: store.get(storeName, []), selectedIds: store.get(selectedStoreName, []) }, (res) => {
        $table.empty().html(res);
    });
    $('.js-selected-count').html(store.get(storeName, []).length);
}

function checkItemBindedCount() {
    if ($('.js-selected-item-binded').length <= 0) {
        $('.has-related').remove();
    }
}
