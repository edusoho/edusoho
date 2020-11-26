import OutFocusMask from './out-focus-mask';

export default class MonitoringEvents {
  constructor(params) {
    this.maskElement = params.maskElement || null;
    this.OutFocusMask = new OutFocusMask(this.maskElement);

    this.activityTimer = null;
    this.ACTIVITY_TIME = 180;

    this.eventMaskElement = null;
    this.eventMaskTimer = null;
    this.EVENT_MASK_TIME = 30;
    this.videoPlayRule = params.videoPlayRule;
    this.taskType = params.taskType;
    this.taskPipe = params.taskPipe;

    this.initEvent();
  }

  initEvent() {
    $('body').off('click', '.js-continue-studying');
    $('body').on('click', '.js-continue-studying', () =>  {
      this.OutFocusMask.continueStudying();
      this.taskPipe._flush({reActive: 1});
      this.taskPipe.absorbedChange(0);
    });
    if (navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
      return;
    }

    if (this.videoPlayRule !== 'auto_pause') {
      return;
    }

    if (this.taskType !== 'video') {
      return;
    }

    this.initMaskElement();
    this.initVisibilitychange();
    this.initActivity();
  }

  initMaskElement() {
    let element = `
      <div class="monitor-event-mask" style="position: fixed; left: 0; right: 0; top: 0; bottom: 0; opacity: 0; display: none;"></div>
    `;
    $('body').append(element);
    this.eventMaskElement = $('.monitor-event-mask');
    this.maskElementShow();
  }

  ineffectiveEvent() { // 触发无效学习
    this.OutFocusMask.initLearStopTips();
    this.taskPipe.absorbedChange(1);
    this.taskPipe._flush();
  }

  triggerEvent(type) { // 触发互踢事件
    this.taskPipe.absorbedChange(1);

    if (type === 'reject_current') {
      this.OutFocusMask.initBanTips();
      return;
    }
    if (type === 'kick_previous') {
      this.OutFocusMask.initAntiBrushTips();
      return;
    }
  }

  initVisibilitychange() {
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'hidden') {
        this.ineffectiveEvent();
      }
    });
  }

  initActivity() { // 监控无操作
    this.afterActivity();
    document.onmousedown = this.afterActivity.bind(this);
    document.onscroll = this.afterActivity.bind(this);
    document.onkeypress = this.afterActivity.bind(this);
    document.onmousemove = this.afterActivity.bind(this);
  }

  afterActivity() {
    this.maskElementHide();
    clearTimeout(this.activityTimer);
    this.activityTimer = null;
    this.activityTimer = setTimeout(() => {
      this.ineffectiveEvent();
    }, this.ACTIVITY_TIME * 1000);
  }

  maskElementShow() {
    clearTimeout(this.eventMaskTimer);
    this.eventMaskTimer = null;
    this.eventMaskTimer = setTimeout(() => {
      this.eventMaskElement.show();
    }, this.EVENT_MASK_TIME * 1000);
  }

  maskElementHide() {
    this.eventMaskElement.hide();
    this.maskElementShow();
  }
}