import DoTestBase from '../widget/do-test-base';

class DoTestpaper extends DoTestBase {
  constructor($container) {
    super($container);
    this.$timePauseDialog = this.$container.find('#time-pause-dialog');
    this.$timer = $container.find('.js-testpaper-timer');
    this._initTimer();
    this._init();
  }

  _init() {
    this.$container.find('.js-testpaper-content').perfectScrollbar();
    this.$container.find('.js-panel-card').perfectScrollbar();
    this.$container.on('click','.js-btn-pause',event=>this._clickBtnPause(event));
    this.$container.on('click','.js-btn-resume',event => this._clickBtnReume(event));
  }

  _initTimer() {
    let self = this;
    if (this.$timer != undefined) {

      this.$timer.timer({
        countdown:true,
        duration: this.$timer.data('time'),
        format: '%H:%M:%S',
        callback: function() {
          self.$container.find('#time-finish-dialog').modal('show');
          clearInterval(self.$usedTimer);
          self.usedTime = self.$timer.data('time') / 60;
          self._submitTest(self.$container.find('[data-role="paper-submit"]').data('url'));
        },
        repeat: true,
        start: function() {
          self.usedTime = 0;
        }
      });
    }
  }

  _clickBtnPause(event) {
    let $btn = $(event.currentTarget).toggleClass('active');
    if($btn.hasClass('active')) {
      this.$timer.timer('pause');
      clearInterval(this.$usedTimer);
      this.$timePauseDialog.modal('show');
    }else {
      this.$timer.timer('resume');
      this._initUsedTimer();
      this.$timePauseDialog.modal('hide');
    }
  }

  _clickBtnReume(event) {
    this.$timer.timer('resume');
    this._initUsedTimer();
    this.$container.find('.js-btn-pause').removeClass('active');
    this.$timePauseDialog.modal('hide');
  }
}

new DoTestpaper($('.js-task-testpaper-body'));