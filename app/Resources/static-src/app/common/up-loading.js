export const upLoading = () => {
  $('.js-up-more-link').on('click', event => {
    let $target = $(event.currentTarget);
    $.get($target.data('url'), html => {
      $(html).find('.infinite-item').prependTo($('.infinite-container'));
      let $upLink = $(html).find('.js-up-more-link');
      if ($upLink.length > 0) {
        $target.data('url', $upLink.data('url'));
      } else {
        $target.remove();
      }
    })
  })
}