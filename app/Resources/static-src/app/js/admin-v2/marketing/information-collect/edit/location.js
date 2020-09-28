if ($('.js-checkbox-group').length) {
    $('.js-checkbox-group').on('click', '.js-location-type', (event) => {
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