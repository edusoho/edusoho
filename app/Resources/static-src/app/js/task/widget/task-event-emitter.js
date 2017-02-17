import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';

export default class TaskEventEmitter {
  constructor({element, startTime, timeStep, dataName}) {
    this.element = $(element);
    this.startTime = startTime;
    this.timeStep = timeStep;
    this.dataName = dataName;
    this.eventUrl = this.element.data('eventUrl');

    this.eventDatas = {};

    this.serverInterval = null;

    if (this.eventUrl === undefined) {
      throw Error('task event url is undefined');
    }

    this.eventMap = {
      receives: {}
    };

    this._registerIframeEvents();
    this.init();
  }

  _registerIframeEvents(){
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

    this._registerReceiveActivityIframeEvents();
    return this;
  }

  _registerReceiveActivityIframeEvents(){
    postal.subscribe({
      channel: 'activity-events',
      topic: '#',
      callback: ({event, data}) => {
        let listeners = this.eventMap.receives[event];
        Object.assign(this.eventDatas, data);
        
        $.post(this.eventUrl, {eventName: event, data: this.eventDatas})
            .done(response => {
              if (typeof listeners !== 'undefined') {
                listeners.forEach(callback => callback(response));
              }
              postal.publish({
                channel: 'task-events',
                topic: '#',
                data: response
              });
            })
            .fail((error) => {
              postal.publish({
                channel: 'task-events',
                topic: '#',
                data: { event: event, error: error }
              });
            });
      }
    });

    return this;
  }

  //发送事件到activity
  emit(event, data) {
    return new Promise((resolve, reject) => {
      $.post(this.eventUrl, {eventName: event, data: data})
      .done((response) => {
        postal.publish({
          channel: 'task-events',
          topic: '#',
          data: { event: response.event, data: response.data }
        });
        resolve(response);
      })
      .fail((error) => {
        reject(error);
      });
    });
  }

  init() {
    window.onbeforeunload = () => {  
      this.clear(); 
      this.flush(this.dataName);
    } 
    this.clear();
    let minute = 60 * 1000;
    this.serverInterval = setInterval(() => this.flush(this.dataName),this.timeStep * minute);
  }

  clear() {
    clearInterval(this.serverInterval);
  }

  flush(eventName) {
    Object.assign(this.eventDatas, {
      'stayTime': {
        'startTime': this.startTime
      }
    });
    this.emit(eventName, this.eventDatas)
      .then(response => {
        this.startTime = response.startTime;
        
        this.receiveFinish(response);
      })
      .catch(() => {
        //
      })
  }

  // 监听activity的事件
  receive(event, callback) {
    this.eventMap.receives[event] = this.eventMap.receives[event] || [];
    this.eventMap.receives[event].push(callback);
  }
}
