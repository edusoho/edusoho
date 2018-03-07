$('body').on('click', '.js-cancel-btn', (event) => {
  const $btn = $(event.currentTarget);
  $.post($btn.data('url'), () => {
    $btn.parents('tr').hide();
  });
});