import Cookies from 'js-cookie';
import notify from 'common/notify';

const INFORMATION_COLLECT_COURSE_SELECTED_IDS = 'informationCollectSelectCourseIds';

let $form = $("#message-search-form");
let $modal = $form.parents('.modal');
let $table = $("#course-table");

$('#chooser-items').on('click', function (e) {
    let courseIds = Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS);
    let length = courseIds.length;
    if (length > 200) {
        notify('danger', Translator.trans('admin_v2.information_collect.course.chooser.limit'));
        $('.save-btn').removeClass('disabled');
        return false;
    }

    if ($('#information-collect-course-select-table').length == 1) {
        $.post($(this).data('url'), { ids: courseIds }, function (res) {
            $('#information-collect-course-select-table').empty().html(res);
            $('.js-selected-count').html(length);
            if (length > 0) {
                notify('success', Translator.trans('admin.course.choose_success_hint'));
            }
        });
    }

    $modal.modal('hide');
});

$modal.on('hidden.bs.modal', function (e) {
    $('.select-target-modal').parent('.modal').modal('show');
});

let deleteVacancy = function (array) {
    $.each(array, function (index, value) {
        if (value == '' || value == null) {
            array.splice(index, 1);
        };
    });
    return array;
};

let pushArrayValue = function (array, targetValue) {
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

let popArrayValue = function (array, targetValue) {
    $.each(array, function (index, value) {
        if (value == targetValue) {
            array.splice(index, 1);
        };
    });
};

let initChecked = function (array) {
    let length = $('.batch-item').length;
    let checked_count = 0;
    courseIds = deleteVacancy(array);

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

$('.courses-list').on('click', '.pagination li', function () {
    let url = $(this).data('url');

    if (typeof (url) !== 'undefined') {
        $.post(url, $form.serialize(), function (data) {
            $('.courses-list').html(data);
            initChecked(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS));
        });
    }
});

$('#search').on('click', function () {
    $.post($form.attr('action'), $form.serialize(), function (data) {
        $('.courses-list').html(data);
        initChecked(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS));
    });
});

let courseIds = new Array();

if (Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS).length > 0) {
    initChecked(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS));
};

$('.courses-list').on('click', '.batch-select', function () {
    let $selectdElement = $(this);

    if (Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS).length > 0) {
        courseIds = deleteVacancy(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS));
    };

    if ($selectdElement.prop('checked') == true) {
        $('.batch-item').prop('checked', true);
        $('.batch-item').each(function (index, el) {
            pushArrayValue(courseIds, $(this).val());
        });
    } else {
        $('.batch-item').prop('checked', false);
        $('.batch-item').each(function (index, el) {
            popArrayValue(courseIds, $(this).val());
        });
    }

    $('#selected-count').text(courseIds.length);
    Cookies.set(INFORMATION_COLLECT_COURSE_SELECTED_IDS, courseIds);

});

$('.courses-list').on('click', '.batch-item', function () {
    let length = $('.batch-item').length;
    let checked_count = 0;

    if (Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS).length > 0) {
        courseIds = deleteVacancy(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS));
    };

    if ($(this).prop('checked') == true) {
        pushArrayValue(courseIds, $(this).val());
    } else {
        popArrayValue(courseIds, $(this).val());
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

    $('#selected-count').text(courseIds.length);
    Cookies.set(INFORMATION_COLLECT_COURSE_SELECTED_IDS, courseIds);
});

$('#clear-cookie').click(function () {
    courseIds = Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS);
    courseIds.splice(0, courseIds.length);
    Cookies.set(INFORMATION_COLLECT_COURSE_SELECTED_IDS, courseIds);
    $('#selected-count').text(0);
    $('input[type=checkbox]').prop('checked', false);
});