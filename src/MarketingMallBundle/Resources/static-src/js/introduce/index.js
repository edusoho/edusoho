
const $flag = $('.js-bottom-flag')
const $document = $(document)
const $pageContainer = $('.js-page-container')

$pageContainer.on('scroll', () => {
  const offsetTop = $flag.offset().top
  const documentHeight = $document.height()

  if ((documentHeight - offsetTop) > 110) {
    $('.js-custom-btn').removeClass('disabled')
    $pageContainer.off('scroll')
  }
})
