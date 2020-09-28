import 'es-jquery-sortable';

$('.selected-form-item-list').sortable({
    'distance': 20,
});

$('.selected-form-item-list').on('click', '.js-item-required', (event) => {
    let $currentCheckbox = $(event.currentTarget);
    let $targetLabel = $($currentCheckbox.data('targetLabel'));
    let $targetItem = $($currentCheckbox.data('targetItem'));

    if ($(event.currentTarget).is(':checked')) {
        $targetLabel.hasClass('control-label-required') ? '' : $targetLabel.addClass('control-label-required');
        $targetItem.data('required', true);
    } else {
        $targetLabel.hasClass('control-label-required') ? $targetLabel.removeClass('control-label-required') : '';
        $targetItem.data('required', false);
    }
});

$('.js-selecte-form-items').on('click', '.js-add-item-btn', (event) => {
    let $targetItem = $($(event.currentTarget).data('targetItem'));
    let items = $('input[name="items"]').val() ? JSON.parse($('input[name="items"]').val()) : [];
    items.push($targetItem.data('code'));

    $targetItem.insertAfter($('.selected-form-item-list').children('.list-group-item:last-child'));
    $targetItem.hasClass('hidden') ? $targetItem.removeClass('hidden') : '';
    $targetItem.data('selected', true);
    $(event.currentTarget).hasClass('disabled') ? '' : $(event.currentTarget).addClass('disabled');
    $('input[name="items"]').val(JSON.stringify(items));
});

$('.selected-form-item-list').on('click', '.js-delete-item-btn', (event) => {
    let $targetItem = $($(event.currentTarget).data('targetItem'));
    let $targetButton = $($(event.currentTarget).data('targetButton'));
    let items = $('input[name="items"]').val() ? JSON.parse($('input[name="items"]').val()) : [];
    items.indexOf($targetItem.data('code')) < 0 ? '' : items.splice(items.indexOf($targetItem.data('code')), 1);

    $targetItem.hasClass('hidden') ? '' : $targetItem.addClass('hidden');
    $targetItem.data('selected', false);
    $targetButton.hasClass('disabled') ? $targetButton.removeClass('disabled') : '';
    items.length > 0 ? $('input[name="items"]').val(JSON.stringify(items)) : $('input[name="items"]').val(null);
});