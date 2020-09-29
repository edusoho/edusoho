import 'store';
import notify from 'common/notify';

let $table = $('#information-collect-select-table');
let type = $table.data('type');
let action = $('input[name="action"]:checked').val();
let storeName = 'information_collect_' + action + '_' + type + '_ids';
let selectedStoreName = 'information_collect_selected_' + action + '_' + type + '_ids';

$('.select-target-modal').on('click', '.pagination li', (event) => {
    getSelectedTargets($(event.currentTarget).data('url'));
});

if (store.get(storeName, []) && store.get(storeName, []).length > 0) {
    getSelectedTargets($table.data('selectedUrl'));
};

$('.js-save-selected-target').on('click', (event) => {
    if ($('.has-related').length > 0) {
        notify('danger', Translator.trans('admin_v2.information_collect.chooser.tips'));
        return;
    }

    store.set(selectedStoreName, store.get(storeName, []));

    let $targetCheckbox = $($(event.currentTarget).data('targetCheckbox'));

    if (store.get(storeName, []).length) {
        $('.js-action-radio-group').find('input[name="' + $(event.currentTarget).data('targetInput') + '"]').val(JSON.stringify(store.get(storeName, [])));
        $targetCheckbox.is(':checked') ? '' : $targetCheckbox.prop('checked', 'checked');
    } else {
        $('.js-action-radio-group').find('input[name="' + $(event.currentTarget).data('targetInput') + '"]').val(null);
        $targetCheckbox.is(':checked') ? $targetCheckbox.removeProp('checked') : '';
    }

    $('.js-action-radio').find('.action-type-group-part .' + $(event.currentTarget).data('targetCount')).html(' '+store.get(storeName, []).length);
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
