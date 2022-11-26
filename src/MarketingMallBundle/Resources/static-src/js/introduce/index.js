
const $flag = $('.js-bottom-flag')
const $document = $(document)
const $pageContainer = $('.js-page-container')
const $startMallBtn =  $('.js-start-mall')

$pageContainer.on('scroll', () => {
  const offsetTop = $flag.offset().top
  const documentHeight = $document.height()

  if ((documentHeight - offsetTop) > 50) {
    $startMallBtn.removeClass('disabled');
    $pageContainer.off('scroll')
  }
});

$startMallBtn.on('click', (event) => {
  if ($startMallBtn.hasClass('disabled')) return

  const $btn = $(event.currentTarget);
  $.post($btn.data('url'), resp => {
    if (resp.success) {
      window.location.href = $btn.data('targetUrl');
    }
  });
});

$startMallBtn.popover({
  trigger: 'hover',
  placement: 'top',
  content: '商城介绍浏览完成后即可使用'
});
