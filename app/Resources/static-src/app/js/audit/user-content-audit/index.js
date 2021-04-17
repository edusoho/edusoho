import ShortLongText from 'app/js/util/short-long-text';
import BatchSelect from 'app/js/util/batch-select';
import ItemConfirm from 'app/js/audit/item-confirm';
import BatchConfirm from 'app/js/audit/batch-confirm';

let $table = $('#audit-table');
let $container = $('#audit-table-container');

new ShortLongText({
  element: $table
});

new BatchSelect({
  element: $container
});

new ItemConfirm({
  element: $container,
  dataRole: 'confirm-pass'
});

new ItemConfirm({
  element: $container,
  dataRole: 'confirm-illegal',
});

new BatchConfirm({
  element: $container,
  dataRole: 'confirm-pass'
});

new BatchConfirm({
  element: $container,
  dataRole: 'confirm-illegal'
});

initDatetimePicker();

function initDatetimePicker() {
  let now = new Date();
  $('[name=startTime]').datetimepicker({
    language: document.documentElement.lang,
    autoclose: true
  });

  $('[name=startTime]').datetimepicker('setStartDate', now);
  $('[name=startTime]').datetimepicker().on('changeDate', function () {
    $('[name=endTime]').datetimepicker('setStartDate', $('[name=startTime]').val().substring(0, 16));
  });

  $('[name=endTime]').datetimepicker({
    autoclose: true,
    language: document.documentElement.lang,
  });

  $('[name=endTime]').datetimepicker('setStartDate', now);

  $('[name=endTime]').datetimepicker().on('changeDate', function () {
    $('[name=startTime]').datetimepicker('setEndDate', $('[name=endTime]').val().substring(0, 16));
  });
}