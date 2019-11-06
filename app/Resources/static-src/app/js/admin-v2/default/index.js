$('.js-show-toggle').on('click', (event) => {
  const $target = $(event.currentTarget);
  $('.js-steps').slideToggle();
  const value = $('.js-toggle-text').text() === '收起' ? '展开': '收起';
  $('.js-toggle-text').text(value);
  $target.find('i').toggleClass('es-icon-keyboardarrowup es-icon-keyboardarrowdown');
});

const $html = $('html');
$('#functionModal').on('shown.bs.modal', (e) =>  {
  $html.css('overflow', 'hidden');
}).on('hidden.bs.modal', (e) => {
  $html.css('overflow', 'scroll');
})


$('.js-entrance-list').on('click', '.js-function-choose', (event) => {
  const $target = $(event.currentTarget);
  $target.toggleClass('active');
})

$('.js-save-btn').on('click', (event) => {
  if ($('.js-function-choose.active').length > 7) {
    cd.message({type: 'danger', message: '最多设置7个快捷入口位'})
  }
})