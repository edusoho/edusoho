import Cookies from 'js-cookie';
import notify from 'common/notify';

const INFORMATION_COLLECT_COURSE_SELECTED_IDS = 'informationCollectSelectCourseIds';
$('.select-target-modal').on('click', '.pagination li', (event) => {
    getCourseSets($(event.currentTarget).data('url'));
});

if (Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS).length > 0) {
    getCourseSets($('#information-collect-course-select-table').data('selectedUrl'));
};

$('.js-save-selected-target').on('click', () => {
    if ($('.has-related').length > 0) {
        notify('danger', Translator.trans('admin_v2.information_collect.chooser.tips'));
        return;
    }

    $('.js-action-radio').find('input[name="courseIds"]').val(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS));
    $('.js-action-radio').find('.selected-course-count').html(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS).length);
    $('.select-target-modal').parent('.modal').modal('hide');
});

$('.select-target-modal').on('click', '.js-selected-item-delete', function (event) {
    let courseIds = Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS);
    courseIds.splice(courseIds.indexOf($(this).parents('tr').data('id').toString()), 1);
    Cookies.set(INFORMATION_COLLECT_COURSE_SELECTED_IDS, courseIds);
    $(this).parents('tr').remove();
    $('.js-selected-count').html(courseIds.length);
    if ($('.js-selected-item-binded').length <= 0) {
        $('.has-related').remove();
    }
});

$('.select-target-modal').on('click', '.js-selected-item-unbind', function (e) {
    $(this).parents('tr').remove();
    $('.js-selected-count').html($('.js-selected-item').length);

});

function getCourseSets(url) {
    $.get(url, { ids: Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS) }, (res) => {
        $('#information-collect-course-select-table').empty().html(res);
    });
    $('.js-selected-count').html(Cookies.getJSON(INFORMATION_COLLECT_COURSE_SELECTED_IDS).length);
}
