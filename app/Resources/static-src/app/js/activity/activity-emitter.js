// import postal from 'postal';
// import 'postal.federation';
// import 'postal.xframe';

export default class ActivityEmitter {
  constructor() {
    // this.sdk = ltcsdk.config();
    // this.messenger = this.sdk.getMessenger();

    this.eventMap = {
      receives: {}
    };

    this._registerIframeEvents();
  }

  _registerIframeEvents(){
    // postal.instanceId('activity');

    // postal.fedx.addFilter([
    //   {
    //     channel: 'activity-events', //发送事件到task parent
    //     topic: '#',
    //     direction: 'out'
    //   },
    //   {
    //     channel: 'task-events',  //接收 task parent 的事件
    //     topic: '#',
    //     direction: 'in'
    //   }
    // ]);

    // postal.fedx.signalReady();

    
    this._registerReceiveTaskParentEvents();

    return this;
  }

  _registerReceiveTaskParentEvents() {
    // this.messenger.on('task-events', (data) => {
    //   let listeners = this.eventMap.receives[data.event];
    //   if (typeof listeners !== 'undefined') {
    //     listeners.forEach(callback => callback(data.data));
    //   }
    // })

    window.addEventListener('message', (e) => {
      if (e.data.channel == 'task-events') {
        let listeners = this.eventMap.receives[e.data.event];
        if (typeof listeners !== 'undefined') {
          listeners.forEach(callback => callback(e.data.data));
        }
      }
    });

    // postal.subscribe({
    //   channel: 'task-events',
    //   topic: '#',
    //   callback: ({event, data}) => {
    //     let listeners = this.eventMap.receives[event];
    //     if (typeof listeners !== 'undefined') {
    //       listeners.forEach(callback => callback(data));
    //     }
    //   }
    // });
  }

  //发送事件到task
  emit(event, data) {
    return new Promise((resolve, reject) => {
      // let message = {
      //   event: event,
      //   data: data
      // };

      // postal.publish({
      //   channel: 'activity-events',
      //   topic: '#',
      //   data: message
      // });
      console.log('activity-events-iframe', event, data);

      // this.messenger.emit({
      //   channel: 'activity-events',
      //   event: event,
      //   data: data
      // });

      window.parent.postMessage({
        channel: 'activity-events',
        event: event,
        data: data
      }, '*');

      // this.messenger.on('task-events', (data) => {
      //   if (data.data.error) {
      //     reject(data.data.error);
      //   } else {
      //     resolve(data.data);
      //   }
      // })

      window.addEventListener('message', (e) => {
        console.log('task-events-iframe', e.data);
        if (e.data.channel == 'task-events') {
          if (e.data.data.error) {
            reject(e.data.data.error);
          } else {
            resolve(e.data.data);
          }
        }
      });

      // let channel = postal.channel('task-events');
      // let subscriber = channel.subscribe('#', (data) => {
      //   if (data.error) {
      //     reject(data.error);
      //   } else {
      //     resolve(data);
      //   }
      //   subscriber.unsubscribe();
      // });
    });
  }

  //监听task的事件
  receive(event, callback) {
    this.eventMap.receives[event] = this.eventMap.receives[event] || [];
    this.eventMap.receives[event].push(callback);
  }
}
