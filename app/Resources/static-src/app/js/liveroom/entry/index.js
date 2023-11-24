import Api from 'common/api';
import DurationStorage from '../../../common/duration-storage';
import MonitoringEvents from '../../task/widget/monitoringEvents';
import { Browser, isMobileDevice } from 'common/utils';

class Live {
  constructor() {
    this.taskId = $('#entry').data('taskId');
    this.courseId = $('#entry').data('courseId');
    this.taskPipeCounter = 0;
    this.pushing = false;
    this.sign = '';
    this.absorbed = 0;
    this.TASK_PIPE_INTERNAL = 60;
    this.intervalId = null;
    this.lastTimestamp = 0;
    this.init();
  }

  init() {
    let role = $('#entry').data('role');
    if (role === 'student' && this.taskId != 0) {
      this.triggerLiveEvent();
    }
  }

  triggerLiveEvent() {
    this._initInterval();

    if (Browser.safari && !isMobileDevice()) {
      this.safariVisibilitychange();
    }
  }

  _clearInterval() {
    clearInterval(this.intervalId);
  }

  _initInterval() {
    this._flush();
    window.onbeforeunload = () => {
      this._clearInterval();
      this._flush();
      if (this.sign.length > 0) {
        localStorage.setItem('flowSign', this.sign);
      }
    };
    this._clearInterval();
    this.intervalId = setInterval(() => this._addPipeCounter(), 1000);
  }

  _flush(param = {}) {
    if (this.pushing) {
      return;
    }
    if (this.sign === '') {
      let customData = {};
      let flowSign = localStorage.getItem('flowSign');
      if (flowSign) {
        this.lastSign = flowSign;
        customData.lastSign = flowSign;
        localStorage.removeItem('flowSign');
      }
      Api.courseTaskEvent.pushEvent({
        params: {
          courseId: this.courseId,
          taskId: this.taskId,
          eventName: 'start',
        },
        data: Object.assign({
          client: 'pc',
        }, customData),
      }).then(res => {
        this.MonitoringEvents = new MonitoringEvents({
          videoPlayRule: this.videoPlayRule,
          taskType: 'live',
          taskPipe: this,
          maskElement: $('body')
        });

        if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'kick_previous') {
          this.MonitoringEvents.triggerEvent('kick_previous');
          return;
        } else if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'reject_current') {
          this.MonitoringEvents.triggerEvent('reject_current');
          this._clearInterval();
          $('[name=classroom]').attr('src', '');
          return;
        }
        this.sign = res.record.flowSign;
        this.record = res.record;
        this._doing(param);
      });
    } else {
      this._doing(param);
    }
  }

  _doing(param = {}) {
    if (this.sign.length === 0) {
      return;
    }
    let data = {
      client: 'pc',
      sign: this.sign,
      duration: this.taskPipeCounter,
      status: this.absorbed,
      lastLearnTime: DurationStorage.get(this.userId, this.fileId),
    };
    if (param.watchTime) {
      let watchData = {
        watchData: {
          duration: param.watchTime,
        }
      };
      data = Object.assign(data, watchData);
    }
    if (param.reActive) {
      data.reActive = param.reActive;
    }
    this.pushing = true;
    Api.courseTaskEvent.pushEvent({
      params: {
        courseId: this.courseId,
        taskId: this.taskId,
        eventName: 'doing',
      },
      data: data,
    }).then(res => {
      this.pushing = false;
      this.record = res.record;
      this.taskPipeCounter = 0;
      this.lastTimestamp = new Date().getTime();
      if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'kick_previous') {
        this.MonitoringEvents.triggerEvent('kick_previous');
      } else if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'reject_current') {
        this.MonitoringEvents.triggerEvent('reject_current');
      }
    }).catch(error => {
      this.pushing = false;
      this._clearInterval();
      cd.message({type: 'danger', message: Translator.trans('task_show.user_login_protect_tip')});
      window.location.href = `/my/course/${this.courseId}`
    });
  }

  absorbedChange(n) {
    this.absorbed = n;
  }

  _addPipeCounter() {
    this.taskPipeCounter++;
    if (this.taskPipeCounter >= this.TASK_PIPE_INTERNAL) {
      this._flush();
    }
  }

  safariVisibilitychange() {
    document.addEventListener('visibilitychange', () => {
      let status = document.visibilityState;
      if (status === 'hidden') {
        this._clearInterval();
      } else if(status === 'visible') {
        this.taskPipeCounter = Math.round((new Date().getTime() - this.lastTimestamp) / 1000);
        this.intervalId = setInterval(() => this._addPipeCounter(), 1000);
      }
    });
  }
}

new Live();