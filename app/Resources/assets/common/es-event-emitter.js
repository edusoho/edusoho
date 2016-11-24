import Emitter from "es6-event-emitter";

export default class EsEmitter extends Emitter {
  constructor() {
    super();
  }

  delay(event, cb, time) {
    time = time || 0;

    let delayCb = function () {
      let args = arguments;
      setTimeout(() => {
        cb.apply(self, [...args]);
      }, time);
    };

    return this.on(event, delayCb);
  }

  once(event, cb) {
    let self = this;
    let onceCb = function () {
      cb.apply(self, [...arguments]);
      self.off(event, onceCb);
    };
    return this.on(event, onceCb);
  }
}