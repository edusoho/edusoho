import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';
import DurationStorage from '../../../common/duration-storage';
import MonitoringEvents from './monitoringEvents';
import Api from 'common/api';
import { Browser, isMobileDevice } from 'common/utils';

export default class TaskPipe {
  constructor(element) {
    this.playerEnd = null;
    this.element = $(element);
    this.eventUrl = this.element.data('eventUrl');
    this.videoPlayRule = this.element.data('videoPlayRule');
    this.learnTimeSec = this.element.data('learnTimeSec');
    this.taskType = this.element.data('taskType');
    this.TASK_PIPE_INTERNAL = this.element.data('learnTimeSec');
    this.userId = this.element.data('userId');
    this.fileId = this.element.data('fileId');
    this.taskId = this.element.data('taskId');
    this.courseId = this.element.data('courseId');
    this.isLogout = false;
    this.taskPipeCounter = 0;
    if (parseInt(this.element.data('lastLearnTime')) != parseInt(DurationStorage.get(this.userId, this.fileId))) {
      DurationStorage.del(this.userId, this.fileId);
      DurationStorage.set(this.userId, this.fileId, this.element.data('lastLearnTime'));
    }
    this.lastLearnTime = DurationStorage.get(this.userId, this.fileId);
    this.sign = '';
    this.record = {};
    this.pushing = false;
    this.waitingEvent = {};
    this.waitingEventData = {};

    this.absorbed = 0;
    this.lastTimestamp = 0;

    if (this.eventUrl === undefined) {
      throw Error('task event url is undefined');
    }

    this.eventDatas = {};
    this.playerMsg = {};
    this.intervalId = null;
    this.lastTime = this.element.data('lastTime');
    this.eventMap = {
      receives: {}
    };

    this._registerChannel();

    if (this.element.data('eventEnable') == 1) {
      this._initInterval();
    }

    this.MonitoringEvents = null;

    if (Browser.safari && !isMobileDevice()) {
      this.safariVisibilitychange();
    }
  }

  _registerChannel() {
    postal.instanceId('task');

    postal.fedx.addFilter([
      {
        channel: 'activity-events', //接收 activity iframe的事件
        topic: '#',
        direction: 'in'
      },
      {
        channel: 'task-events',  // 发送事件到activity iframe
        topic: '#',
        direction: 'out'
      }
    ]);

    postal.subscribe({
      channel: 'activity-events',
      topic: '#',
      callback: ({event, data}) => {
        console.log(event, data);
        this.playerEnd = (event === 'finish');
        if (event == 'finish' && this.pushing) {
          this.waitingEvent = {event: event, data: data};
          this.waitingEventData[event] = data;
          return;
        }
        this.eventDatas[event] = data;
        this._flush(data);
      }
    });

    return this;
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

  _addPipeCounter() {
    this.taskPipeCounter++;
    if (this.taskPipeCounter >= this.TASK_PIPE_INTERNAL) {
      this._flush();
    }
  }

  _clearInterval() {
    clearInterval(this.intervalId);
  }

  _flush(param = {}) {
    if (this.pushing) {
      //同时出现的doing需要忽略，比如播放行为
      return ;
    }
    if (this.isLogout) return;
    let clientType = 'pc';
    if (isMobileDevice()) {
      clientType = 'wap';
    }
    if (this.sign === '') {
      let customData = {};
      let release = param.release || 0;
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
          release: release,
          client : clientType,
        }, customData),
      }).then(res => {
        //对于只需要一次性释放的逻辑，不弹遮罩，而且flow.active == 0
        if (release === 1) {
          this._clearInterval();
          return ;
        }
        this.MonitoringEvents = new MonitoringEvents({
          videoPlayRule: this.videoPlayRule,
          taskType: this.taskType,
          taskPipe: this
        });

        if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'kick_previous') {
          this.MonitoringEvents.triggerEvent('kick_previous');
          return ;
        } else if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'reject_current') {
          if (isMobileDevice()) {
            return;
          }
          this.MonitoringEvents.triggerEvent('reject_current');
          this._clearInterval();
          this.element.attr('src', '');
          return;
        }
        this.sign = res.record.flowSign;
        this.record = res.record;
        this._doing(param);
      });
    } else{
      this._doing(param);
    }

    // let ajax = $.post(this.eventUrl, { data: { lastTime: this.lastTime, lastLearnTime: DurationStorage.get(this.userId, this.fileId), events: this.eventDatas}})
    //   .done((response) => {
    //     this._publishResponse(response);
    //     this.eventDatas = {};
    //     this.lastTime = response.lastTime;
    //     if (response && response.result && response.result.status) {
    //       if (param.data) {
    //         response.playerMsg = param.data.playerMsg;
    //       }
    //
    //       let listners = this.eventMap.receives[response.result.status];
    //       if (listners) {
    //         for (var i = listners.length - 1; i >= 0; i--) {
    //           let listner = listners[i];
    //           listner(response);
    //         }
    //       }
    //     }
    //   })
    //   .fail((error) => {
    //     if (error.status == 403 && !this.isLogout) {
    //       this._clearInterval();
    //       cd.message({ type: 'danger', message: Translator.trans('task_show.user_login_protect_tip') });
    //       this.isLogout = true;
    //       window.location.href = '/logout';
    //     }
    //   });
    //
    // return ajax;
  }

  _doing(param = {}) {
    if (this.sign.length === 0) {
      return;
    }
    let clientType = 'pc';
    if (isMobileDevice()) {
      clientType = 'wap';
    }
    let data = {
      client: clientType,
      sign: this.sign,
      duration: this.taskPipeCounter,
      status: this.absorbed,
      lastLearnTime: DurationStorage.get(this.userId, this.fileId),
      events: this.eventDatas,
    };
    console.log(param);
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
      if (res && res.taskResult && res.taskResult.status) {
        if (param.data) {
          res.playerMsg = param.data.playerMsg;
        }

        let listners = this.eventMap.receives[res.taskResult.status];
        if (listners) {
          for (var i = listners.length - 1; i >= 0; i--) {
            let listner = listners[i];
            listner(Object.assign(res, {waitingEventData: this.waitingEventData, playerEnd: this.playerEnd}));
          }
        }
      }

      if (this.waitingEvent.event) {
        this.eventDatas = this.waitingEventData;
        this._flush(this.waitingEvent.data);
        this.waitingEvent = {};
        this.waitingEventData = {};
      }
    }).catch(error => {
      this.pushing = false;
      this._clearInterval();
    });
  }

  _publishResponse(response) {
    postal.publish({
      channel: 'task-events',
      topic: '#',
      data: {event: response.event, data: response.data}
    });
  }

  addListener(event, callback) {
    this.eventMap.receives[event] = this.eventMap.receives[event] || [];
    this.eventMap.receives[event].push(callback);
  }

  absorbedChange(n) {
    this.absorbed = n;
  }

  safariVisibilitychange() {
    document.addEventListener('visibilitychange', () => {
      if (['live'].includes(this.taskType) ) {
        return ;
      }

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
