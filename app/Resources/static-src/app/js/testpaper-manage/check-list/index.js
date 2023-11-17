$('[data-tooltip="tooltip"]').tooltip({
  trigger: 'hover',
});

const $list = $('.js-task-list');
$list.on('click', '.pagination li', e => {
  const url = $(e.currentTarget).data('url');

  $.get(url, html => {
    $list.html(html);
  });
});
