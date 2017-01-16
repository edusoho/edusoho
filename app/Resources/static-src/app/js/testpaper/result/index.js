import DoTestBase from '../widget/do-test-base';
import { initScrollbar,testpaperCardFixed,testpaperCardLocation,onlyShowError } from '../widget/tool';

initScrollbar();
testpaperCardFixed();
testpaperCardLocation();
onlyShowError();

class ShowResult extends DoTestBase {
  constructor($container) {
    super($container);
    
  }
}

new ShowResult($('.js-task-testpaper-body-iframe'));

$('.js-testpaper-redo-timer').timer({
  countdown:true,
  duration: $('.js-testpaper-redo-timer').data('time'),
  format: '%H:%M:%S',
  callback: function() {
    $('#finishPaper').attr('disabled',false);
  },
  repeat: true,
  start: function() {
    self.usedTime = 0;
  }
});
