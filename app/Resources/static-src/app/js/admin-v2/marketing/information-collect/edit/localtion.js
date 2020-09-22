console.log('.checkbox-group');
if ($('.checkbox-group').length) {
    $('.checkbox-group').on('click', 'input[name="actionType"]', (event) => {
        let $group = $(event.currentTarget).parent().find('.target-type-checkbox-group');
        if ($group.hasClass('hidden')) {
            $group.removeClass('hidden');
        }
        let $siblingGroup = $(event.currentTarget).parents('.action-type-group').siblings('.action-type-group').find('.target-type-checkbox-group');

        if (!$siblingGroup.hasClass('hidden')) {
            $siblingGroup.addClass('hidden');
        }
    });
}


// name="actionType"