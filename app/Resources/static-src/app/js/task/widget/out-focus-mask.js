import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';
export default class OutFocusMask {
  constructor($element = null) {
    this.$element = $element === null ?  $('.all-wrapper') : $element;
    this.mask = `
            <div class="out-focus-mask">
                <div class="content">
                    <div class="tips"></div>
                    <div class="continue-studying">
                        <button class="btn btn-primary js-continue-studying">${Translator.trans('course.task.out_focus_mask.continue_studying')}</button>
                    </div>
                </div>
            </div>`;
    this.mask1 = `
            <div class="out-focus-mask">
                <div class="content">
                    <div class="tips"></div>
                </div>
            </div>`;

    this.initEvent();
  }

  initEvent() {
    // this.$element.off('click', '.js-continue-studying');
    // this.$element.on('click', '.js-continue-studying', () => this.continueStudying());
    this._registerChannel();
  }

  validateMask() {
    return this.$element.find('.out-focus-mask').length > 0;
  }

  initLearStopTips() {
    if (this.validateMask()) {
      return;
    }

    this.$element.append(this.mask);
    this.$element.find('.out-focus-mask .content .tips').html(Translator.trans('course.task.out_focus_mask.stop.tips'));
    this.popAfter();
  }

  initAntiBrushTips() {
    if (this.validateMask()) {
      this.destroyMask();
    }

    this.$element.append(this.mask);
    this.$element.find('.out-focus-mask .content .tips').html(Translator.trans('course.task.out_focus_mask.anti_brush.tips'));
    this.popAfter();
  }

  initBanTips() {
    if (this.validateMask()) {
      this.destroyMask();
    }

    this.$element.append(this.mask1);
    this.$element.find('.out-focus-mask .content .tips').html(Translator.trans('course.task.out_focus_mask.anti_brush.tips'));
    this.popAfter();
  }

  continueStudying() {
    this.destroyMask();
    this._publishResponse('play');
  }

  destroyMask() {
    this.$element.find('.out-focus-mask').remove();
  }

  popAfter() {
    this._publishResponse('pause');
  }

  _registerChannel() {
    postal.instanceId('task');

    postal.fedx.addFilter([
      {
        channel: 'task-events',  // 发送事件到activity iframe
        topic: 'monitoringEvent',
        direction: 'out'
      }
    ]);

    return this;
  }

  _publishResponse(type) {
    postal.publish({
      channel: 'task-events',
      topic: 'monitoringEvent',
      data: type
    });
  }
}