import Messenger from "es-messenger";

export default class TaskEventEmitter {
  constructor(element) {
    this.element = $(element);
    this.eventUrl = this.element.data('eventUrl');
    if (this.eventUrl === undefined) {
      throw Error('task event url is undefined');
    }

    this.eventMap = {
      receives: {}
    };

    this.receiveMessenger = new Messenger('TaskMessenger', 'ActivityEvent');
    this.receiveMessenger.addTarget(this.element.get(0).contentWindow, 'task-content-iframe');

    this.receiveMessenger.listen(message => {
      let { event, data } = JSON.parse(message);
      console.log("event, data", event, data);
      let listeners = this.eventMap.receives[event];

      $.post(this.element.data('eventUrl'), {eventName: event, data: data})
        .done(response => {
          if (typeof listeners !== 'undefined') {
            listeners.forEach(callback => callback(response));
          }
          
          this.receiveMessenger.send(JSON.stringify(response));
        })
        .fail((error) => {
          this.receiveMessenger.send(JSON.stringify({ event: event, error: error }));
        })
    });

    this.emitMessenger = new Messenger('TaskMessenger', 'TaskEvent');
    this.emitMessenger.addTarget(this.element.get(0).contentWindow, 'task-content-iframe');

  }

  //发送事件到activity
  emit(event, data) {
    return new Promise((resolve, reject) => {
      $.post(this.eventUrl, {eventName: event, data: data})
      .done((response) => {
        this.emitMessenger.send(JSON.stringify({event: response.event, data: response.data}));
        resolve(response);
      })
      .fail((error) => {
        reject(error);
      });
    });
  }

  // 监听activity的事件
  receive(event, callback) {
    this.eventMap.receives[event] = this.eventMap.receives[event] || [];
    this.eventMap.receives[event].push(callback);
  }
}
