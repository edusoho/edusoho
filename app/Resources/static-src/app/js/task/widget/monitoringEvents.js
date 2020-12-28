import OutFocusMask from './out-focus-mask';
import { Browser, isMobileDevice } from 'common/utils';
import screenfull from 'es-screenfull';

export default class MonitoringEvents {
  constructor(params) {
    this.maskElement = params.maskElement || null;
    this.OutFocusMask = new OutFocusMask(this.maskElement);

    this.activityTimer = null;
    this.ACTIVITY_TIME = 1200;

    this.eventMaskElement = null;
    this.eventMaskTimer = null;
    this.EVENT_MASK_TIME = 30;
    this.videoPlayRule = params.videoPlayRule;
    this.taskType = params.taskType;
    this.taskPipe = params.taskPipe;

    this.lastFullScreenState = screenfull.isFullscreen;
    this.fullScreenTimer = null;

    this.initEvent();
  }

  initEvent() {
    $('body').off('click', '.js-continue-studying');
    $('body').on('click', '.js-continue-studying', () =>  {
      this.OutFocusMask.continueStudying();
      this.taskPipe._flush({reActive: 1});
      this.taskPipe.absorbedChange(0);
    });

    if (isMobileDevice()) {
      return;
    }

    if (this.videoPlayRule !== 'auto_pause') {
      return;
    }

    if (this.taskType !== 'video') {
      return;
    }

    if (Browser.safari) {
      this.safariResetFullScreenState();
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
        if (Browser.safari && !this.lastFullScreenState && screenfull.isFullscreen) {
          this.lastFullScreenState = screenfull.isFullscreen;
          return;
        }
        this.ineffectiveEvent();
      }
    });
  }

  safariResetFullScreenState() {
    window.addEventListener('resize', () => {
      if (!this.fullScreenTimer) {
        this.fullScreenTimer = setTimeout(() => {
          this.lastFullScreenState = screenfull.isFullscreen;
          clearTimeout(this.fullScreenTimer);
          this.fullScreenTimer = null;
        }, 66);
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