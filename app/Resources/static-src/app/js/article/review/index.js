import ShortLongText from 'app/js/util/short-long-text';
import BatchSelect from 'app/js/util/batch-select';
import BatchDelete from 'app/js/util/batch-delete';
import ItemDelete from 'app/js/util/item-delete';

let $container = $('#review-table-container');
let $table = $("#review-table");

new ShortLongText({
  element: $table
});

new BatchSelect({
  element: $container
});

new BatchDelete({
  element: $container
});

new ItemDelete({
  element: $container
});