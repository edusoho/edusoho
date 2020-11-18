import Api from 'common/api';
import OutFocusMask from './out-focus-mask';

export default class MonitoringEvents {
  constructor() {
    this.initEvent();
    this.OutFocusMask = new OutFocusMask();
  }

  initEvent() {
    this._initVisibilitychange();
  }

  _initVisibilitychange() { // tab　监控（切换，最小化）
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'hidden') {
        // 触发无效学习事件
        this.OutFocusMask.initLearStopTips();
      }
    });
  }
}