$('.js-exit-reason').each((index, elem) => {
  const $elem = $(elem);
  $elem.popover({
    placement: 'top',
    content: $elem.text(),
    trigger: 'hover'
  });
});
