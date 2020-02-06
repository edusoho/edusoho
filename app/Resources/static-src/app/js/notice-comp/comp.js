class Component {
  constructor() {
    this.handler = {};
  }

  trigger(eventName, callback) {
    if (typeof this[eventName] === 'function') {
      this[eventName](callback);
    } else {
      throw new Error(`${eventName} event does not exist`);
    }
  }

  on(eventName, callback) {
    this.handler[eventName] = callback;

    return this;
  }

  emit(eventName) {
    let args = [].slice.call(arguments, 1);

    this.handler[eventName] && this.handler[eventName](...args);
  }
}

export default Component;