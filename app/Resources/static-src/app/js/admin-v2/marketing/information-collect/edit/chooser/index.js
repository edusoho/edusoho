import 'store';
import notify from 'common/notify';

let $form = $("#search-form");
let $modal = $form.parents('.modal');
let type = $form.data('type');

let action = $('input[name="action"]:checked').val();
let storeName = 'information_collect_' + action + '_' + type + '_ids';   
let selectedStoreName = 'information_collect_selected_' + action + '_' + type + '_ids';

let targetIds = new Array();

if (store.get(storeName, []) && store.get(storeName, []).length > 0) {
    initChecked(store.get(storeName, []));
}

$('#chooser-items').on('click', function (e) {
    let targetIds = store.get(storeName, []);
    let length = targetIds.length;
    if (length > 200) {
        notify('danger', Translator.trans('admin_v2.information_collect.chooser.limit'));
        $('.save-btn').removeClass('disabled');
        return false;
    }

    if ($('#information-collect-select-table').length == 1) {
        $.get($(this).data('url'), { action: action, ids: targetIds, selectedIds: store.get(selectedStoreName, []) }, function (res) {
            $('#information-collect-select-table').empty().html(res);
            $('.js-selected-count').html(length);
            
            notify('success', Translator.trans('admin_v2.information_collect.chooser.success_hint'));
        });
    }

    $modal.modal('hide');
});

$modal.on('hidden.bs.modal', function (e) {
    $('.select-target-modal').parent('.modal').modal('show');
});

function deleteVacancy(array) {
    $.each(array, function (index, value) {
        if (value == '' || value == null) {
            array.splice(index, 1);
        };
    });
    return array;
};

function pushArrayValue(array, targetValue) {
    let isExist = false;
    $.each(array, function (index, value) {
        if (value == targetValue) {
            isExist = true;
            return;
        };
    });

    if (!isExist && !isNaN(targetValue)) {
        array.push(targetValue);
    };
};

function popArrayValue(array, targetValue) {
    $.each(array, function (index, value) {
        if (value == targetValue) {
            array.splice(index, 1);
        };
    });
};

function initChecked(array) {
    let length = $('.batch-item').length;
    let checked_count = 0;
    targetIds = deleteVacancy(array);

    $('#selected-count').text(array.length);

    $.each(array, function (index, value) {
        $('#batch-item-' + value).prop('checked', true);
    });

    $('.batch-item').each(function () {
        if ($(this).is(':checked')) {
            checked_count++;
        };

        if (length == checked_count) {
            $('.batch-select').prop('checked', true);
        } else {
            $('.batch-select').prop('checked', false);
        }
    });
};

$('.search-list').on('click', '.pagination li', function () {
    let url = $(this).data('url');

    if (typeof (url) !== 'undefined') {
        $.get(url, $form.serialize(), function (data) {
            $('.search-list').html(data);
            initChecked(store.get(storeName, []));
        });
    }
});

$('#search').on('click', function () {
    $.get($form.attr('action'), $form.serialize(), function (data) {
        $('.search-list').html(data);
        initChecked(store.get(storeName, []));
    });
});

$('.search-list').on('click', '.batch-select', function () {
    let $selectdElement = $(this);

    if (store.get(storeName, []) && store.get(storeName, []).length > 0) {
        targetIds = deleteVacancy(store.get(storeName, []));
    };

    if ($selectdElement.prop('checked') == true) {
        $('.batch-item').prop('checked', true);
        $('.batch-item').each(function (index, el) {
            pushArrayValue(targetIds, $(this).val());
        });
    } else {
        $('.batch-item').prop('checked', false);
        $('.batch-item').each(function (index, el) {
            popArrayValue(targetIds, $(this).val());
        });
    }

    $('#selected-count').text(targetIds.length);
    store.set(storeName, targetIds);

});

$('.search-list').on('click', '.batch-item', function () {
    let length = $('.batch-item').length;
    let checked_count = 0;

    if (store.get(storeName, []) && store.get(storeName, []).length > 0) {
        targetIds = deleteVacancy(store.get(storeName, []));
    };

    if ($(this).prop('checked') == true) {
        pushArrayValue(targetIds, $(this).val());
    } else {
        popArrayValue(targetIds, $(this).val());
    }

    $('.batch-item').each(function () {
        if ($(this).is(':checked')) {
            checked_count++;
        };

        if (length == checked_count) {
            $('.batch-select').prop('checked', true);
        } else {
            $('.batch-select').prop('checked', false);
        }
    });

    $('#selected-count').text(targetIds.length);
    store.set(storeName, targetIds);
});

$('#clear-storage').click(function () {
    targetIds = store.get(storeName, []);
    targetIds.splice(0, targetIds.length);
    store.set(storeName, targetIds);
    $('#selected-count').text(0);
    $('input[type=checkbox]').prop('checked', false);
});