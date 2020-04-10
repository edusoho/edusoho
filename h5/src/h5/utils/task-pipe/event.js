class Event {
  constructor() {
    this.eventBus = {};
  }

  /*
   * add event listner
   */
  on(event, callback) {
    if (!callback) {
      throw new Error('event listener need a callback!');
    }
    this.eventBus[event] = this.eventBus[event] ? [...this.eventBus[event], callback] : [callback];
    return this;
  }

  /*
   * invoke callback of event
   */
  trigger(event, data) {
    if (this.eventBus[event]) {
      this.eventBus[event].map(cb => cb(data));
    }
    return this;
  }
}

export default Event;
