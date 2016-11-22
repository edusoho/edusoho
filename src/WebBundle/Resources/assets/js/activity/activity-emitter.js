import Messenger from 'es-messenger';

export default class ActivityEmitter {

  constructor(){
    this.emitMessenger = new Messenger('task-content-iframe', 'ActivityEvent');
    this.emitMessenger.addTarget(window.parent, 'parent');

    this.eventMap = {
      receives: {}
    };

    this.receiveMessenger = new Messenger('task-content-iframe', 'TaskEvent');
    this.receiveMessenger.addTarget(window.parent, 'parent');
    this.receiveMessenger.listen(message => {
      let {event, data} = JSON.parse(message);
      let listeners = this.eventMap.receives[event];
      if (typeof listeners !== 'undefined') {
        listeners.forEach(callback => callback(data));
      }
    });
  }

  //发送事件到task
  emit(event, data) {
    return new Promise((resolve, reject) => {
      let message = JSON.stringify({
        event: event,
        data: data
      });

      this.emitMessenger.send(message);

      this.emitMessenger.listen((message) => {
        message = JSON.parse(message);
        let listenEvent = message.event;
        if(listenEvent === event){
          if(message.error){
            reject(message.error);
          }else {
            resolve(message.data);
          }
        }
      });
    });
  }

  //监听task的事件
  receive(event, callback){
    this.eventMap.receives[event] = this.eventMap.receives[event] || [];
    this.eventMap.receives[event].push(callback);
  }
}