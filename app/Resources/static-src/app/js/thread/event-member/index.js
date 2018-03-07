let $element = $('#event-member');

let memberSum = $element.data('sum');
let currentPage = 1;

$element.on('click', '.js-members-expand', (e) => {
  let $target = $(e.currentTarget);

  if ($target.data('expandAll')) {
    $('.js-join-members').fadeIn(500);
    $('.js-members-expand').hide();
    $('.js-members-collapse').show();
  } else {
    $.get($target.data('url'), {page: currentPage + 1}, (result) => {
      $('.js-join-members').append(result);
      let length = $('.js-join-members > span').length;
      
      if (memberSum == length) {
        $target.data('expandAll', true).hide();
        $('.js-members-collapse').show();
      } else {
        currentPage = currentPage + 1;
      }
    });
  }
});

$element.on('click', '.js-members-collapse', () => {
  $('.js-join-members').fadeOut(500);
  $('.js-members-expand').show();
  $('.js-members-collapse').hide();
});