import Messenger from 'es-messenger';

export default class ActivityEmitter {

  emit(event, data) {
    return new Promise((resolve, reject) => {
      let messenger = new Messenger('task-content-iframe', 'ActivityEvent');
      let message = JSON.stringify({
        event: event,
        data: data
      });

      messenger.addTarget(window.parent, 'parent');
      messenger.send(message);

      messenger.listen((message) => {
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

}