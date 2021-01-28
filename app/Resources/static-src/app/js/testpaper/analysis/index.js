import {
  initScrollbar,
  testpaperCardFixed,
  testpaperCardLocation,
} from 'app/js/testpaper/widget/part';
import CopyDeny from 'app/js/testpaper/widget/copy-deny';

initScrollbar();
testpaperCardFixed();
testpaperCardLocation();

new CopyDeny();

$('.js-analysis').click(function(){
  let self = $(this);
  self.addClass('hidden');
  self.siblings('.js-analysis.hidden').removeClass('hidden');
  self.closest('.js-testpaper-question').find('.js-testpaper-question-analysis').slideToggle();
});
