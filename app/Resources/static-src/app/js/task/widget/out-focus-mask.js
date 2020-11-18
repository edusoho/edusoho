export default class OutFocusMask {
  constructor(element) {
    this.$element = $(element);
    this.mask = `
            <div class="out-focus-mask">
                <div class="content">
                    <div class="tips"></div>
                    <div class="continue-studying">
                        <button class="btn btn-primary js-continue-studying">${Translator.trans('course.task.out_focus_mask.continue_studying')}</button>
                    </div>
                </div>
            </div>`;

    this.initEvent();
  }

  initEvent() {
    this.$element.on('load', (event) => {
      this.$element.contents().off('click', '.js-continue-studying');
      this.$element.contents().on('click', '.js-continue-studying', () => this.continueStudying());
    });
  }

  validateMask() {
    return this.$element.contents().find('.out-focus-mask').length > 0;
  }

  initLearStopTips() {
    if (this.validateMask()) {
      return;
    }

    this.$element.contents().find('body').append(this.mask);
    this.$element.contents().find('.out-focus-mask .content .tips').html(Translator.trans('course.task.out_focus_mask.stop.tips'));
    this.popAfter();
  }

  initAntiBrushTips() {
    if (this.validateMask()) {
      return;
    }

    this.$element.contents().find('body').append(this.mask);
    this.$element.contents().find('.out-focus-mask .content .tips').html(Translator.trans('course.task.out_focus_mask.anti_brush.tips'));
    this.popAfter();
  }

  continueStudying() {
    this.destroyMask();
    console.log('player 播放事件');
    // player 播放事件
    // doing 事件, 并刷新计时
  }

  destroyMask() {
    this.$element.contents().find('.out-focus-mask').remove();
  }

  popAfter() {
    console.log('player 暂停事件');
    // player 暂停事件
    // doing 事件, 并刷新计时
  }
}