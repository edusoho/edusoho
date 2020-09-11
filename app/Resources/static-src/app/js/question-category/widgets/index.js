import { toggleIcon } from 'app/common/widget/chapter-animate';

$('.js-toggle-show').on('click', (event) => {
  let $this = $(event.target);
  let $sort = $this.closest('.js-sortable-item');
  $sort.nextUntil('.js-sortable-item').animate({
    height: 'toggle',
    opacity: 'toggle'
  }, 'normal');

  toggleIcon($sort, 'cd-icon-add', 'cd-icon-remove');
});

$(document).ready(() => {
  let $categorySearch = $(".js-category-search.active");
  if ($categorySearch.length > 0) {
    $categorySearch.parents('.js-sortable-list').show();
    $categorySearch.parents().find('.js-toggle-show.cd-icon-add').removeClass('cd-icon-add').addClass('cd-icon-remove');
  }
});
