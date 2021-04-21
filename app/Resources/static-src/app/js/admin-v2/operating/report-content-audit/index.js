import ShortLongText from 'app/js/util/short-long-text';
import BatchSelect from 'app/js/util/batch-select';

let $container = $('#audit-table-container');
let $table = $("#audit-table");

new ShortLongText({
  element: $table
});

new BatchSelect({
  element: $container
});

$('.js-table-item-operate').click((element) => {
  showConfirmModal($(element.currentTarget).data('url'));
});

$('.js-batch-operate-btn').click((element) => {
  let ids = getBatchOperateIds();
  if (!ids.length) {
    cd.message({ type: 'danger', message: Translator.trans('admin_v2.operation.audit_center.batch_operate_tips') });

    return;
  }

  showConfirmModal($(element.currentTarget).data('url'), { ids: JSON.stringify(ids), status: $(element.currentTarget).data('status') });
})

function showConfirmModal(url, params = {}) {
  $('#modal').html('');
  $.get(url, params, (resp) => {
    $('#modal').modal('show').html(resp);
  });
}


function getBatchOperateIds() {
  let ids = [];
  $('#audit-table').find('[data-role=batch-item]:checked').each(function () {
    ids.push(this.value);
  });

  return ids;
}
