let $applyModal = $('#attachment-modal');
let $apply = $('.apply-moda-apply');


$apply.on('click', function (e) {
    let $name = $('.apply-moda-name').val();
    let $mobile = $('.apply-moda-mobile').val();
    let $success = $('.apply-success');
    let $form = $('.apply-modal-form');
    if ($name && $mobile) {
        $success.css('display', 'block');
        $form.css('display', 'none');
    } else {
        $('.apply-moda-name').css('borderColor', 'red');
        $('.name-tips').css('display', 'block');
        $('.apply-moda-mobile').css('borderColor', 'red');
        $('.mobile-tips').css('display', 'block');
    }
})

let $knowBtn = $('.apply-moda-btn')
$knowBtn.on('click', function (e) {
    let $target = $(e.currentTarget);
    let $modal = $('#modal');
    $modal.load($target.data('url'));
    $modal.modal('show');
    $applyModal.modal('hide');
})
