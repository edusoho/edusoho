import Emitter from 'component-emitter';
// import Emitter from "es6-event-emitter";

export default class EsEmitter extends Emitter {

  delay(event, cb, time = 0) {
    let delayCb = function () {
      setTimeout(() => {
        cb.apply(self, [...arguments]);
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
