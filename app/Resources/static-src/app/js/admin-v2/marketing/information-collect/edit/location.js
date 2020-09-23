import Cookies from 'js-cookie';

initSelectTargetCookies();
if ($('.js-checkbox-group').length) {
    $('.js-checkbox-group').on('click', 'input[name="actionType"]', (event) => {
        let $group = $(event.currentTarget).parent().find('.js-target-type-checkbox-group');
        if ($group.hasClass('hidden')) {
            $group.removeClass('hidden');
        }
        let $siblingGroup = $(event.currentTarget).parents('.action-type-group').siblings('.action-type-group').find('.js-target-type-checkbox-group');

        if (!$siblingGroup.hasClass('hidden')) {
            $siblingGroup.addClass('hidden');
        }
    });
}

function initSelectTargetCookies() {
    if (Cookies.getJSON('informationCollectSelectCourseIds').length > 0) {
        Cookies.set('informationCollectSelectCourseIds', []);
    }
}

