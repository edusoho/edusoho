import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';
import DurationStorage from '../../../common/duration-storage';
import MonitoringEvents from './monitoringEvents';
import Api from 'common/api';

export default class TaskPipe {
  constructor(element) {
    this.element = $(element);
    this.eventUrl = this.element.data('eventUrl');
    this.videoPlayRule = this.element.data('videoPlayRule');
    this.learnTimeSec = this.element.data('learnTimeSec');
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
      // this._flush({ type: 'beforeunload' });
      localStorage.setItem('flowSign', this.sign);
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
    if (this.isLogout) return;
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
          client : 'pc',
        }, customData),
      }).then(res => {
        this.MonitoringEvents = new MonitoringEvents({
          videoPlayRule: this.videoPlayRule,
          taskPipe: this
        });

        if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'kick_previous') {
          this.MonitoringEvents.triggerEvent('kick_previous');
          return ;
        } else if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'reject_current') {
          this.MonitoringEvents.triggerEvent('reject_current');
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
    let data = {
      client: 'pc',
      sign: this.sign,
      duration: this.taskPipeCounter,
    };
    if (param.watchTime) {
      let watchData = {
        watchData: {
          duration: param.watchTime,
        }
      };
      data = Object.assign(data, watchData)
    }
    Api.courseTaskEvent.pushEvent({
      params: {
        courseId: this.courseId,
        taskId: this.taskId,
        eventName: 'doing',
      },
      data: data,
    }).then(res => {
      this.record = res.record;
      this.taskPipeCounter = 0;
      if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'kick_previous') {
        this.MonitoringEvents.triggerEvent('kick_previous');
      } else if (!res.learnControl.allowLearn && res.learnControl.denyReason === 'reject_current') {
        this.MonitoringEvents.triggerEvent('reject_current');
      }
    }).catch(error => {
      this._clearInterval();
      cd.message({ type: 'danger', message: Translator.trans('task_show.user_login_protect_tip') });
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
}
