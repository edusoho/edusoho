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