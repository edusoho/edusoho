import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';
import DurationStorage from '../../../common/duration-storage';

export default class TaskPipe {
  constructor(element) {
    this.element = $(element);
    this.eventUrl = this.element.data('eventUrl');
    this.learnTimeSec = this.element.data('learnTimeSec');
    this.userId = this.element.data('userId');
    this.fileId = this.element.data('fileId');
    this.isLogout = false;
    if (parseInt(this.element.data('lastLearnTime')) != parseInt(DurationStorage.get(this.userId, this.fileId))) {
      DurationStorage.del(this.userId, this.fileId);
      DurationStorage.set(this.userId, this.fileId, this.element.data('lastLearnTime'));
    }
    this.lastLearnTime = DurationStorage.get(this.userId, this.fileId);

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
    window.onbeforeunload = () => {
      this._clearInterval();
      this._flush();
    };
    this._clearInterval();
    this.intervalId = setInterval(() => this._flush(), this.learnTimeSec*1000);
  }

  _clearInterval() {
    clearInterval(this.intervalId);
  }

  _flush(param = {}) {
    if (this.isLogout) return;

    let ajax = $.post(this.eventUrl, { data: { lastTime: this.lastTime, lastLearnTime: DurationStorage.get(this.userId, this.fileId), events: this.eventDatas}})
      .done((response) => {
        this._publishResponse(response);
        this.eventDatas = {};
        this.lastTime = response.lastTime;
        if (response && response.result && response.result.status) {
          if (param.data) {
            response.playerMsg = param.data.playerMsg;
          }
          
          let listners = this.eventMap.receives[response.result.status];
          if (listners) {
            for (var i = listners.length - 1; i >= 0; i--) {
              let listner = listners[i];
              listner(response);
            }
          }
        }
      })
      .fail((error) => {
        if (error.status == 403 && !this.isLogout) {
          this._clearInterval();
          cd.message({ type: 'danger', message: Translator.trans('task_show.user_login_protect_tip') });
          this.isLogout = true;
          window.location.href = '/logout';
        }
      });

    return ajax;
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
