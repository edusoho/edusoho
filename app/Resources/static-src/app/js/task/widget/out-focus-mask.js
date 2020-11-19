import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';
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
    this._registerChannel();
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
    this._publishResponse('play');
  }

  destroyMask() {
    this.$element.contents().find('.out-focus-mask').remove();
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