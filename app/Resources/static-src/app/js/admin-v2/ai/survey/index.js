let $modal = $('#modal');

$modal.on('click', '.js-close-btn', event => {
  $modal.modal('hide');
});
