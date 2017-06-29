import ActivityEmitter from "../activity-emitter";
export default class LiveShow {
  constructor() {
    this.interval = 1;
    this._startEvent();
    this._countdownEvent();
  }

  _startEvent() {
      let self = this;
      let emitter = new ActivityEmitter();
      $(".js-start-live").on("click", function () {
          if (!self.started) {
              this.started = true;
              emitter.emit('start', {}).then(() => {
                  console.log('live.start');
              }).catch((error) => {
                  console.error(error);
              });
          }
      });
  }

  _countdownEvent() {
    let $countdown = $('#countdown');
    if ($countdown.length == 0) return;

    this.timeRemain = $countdown.data('timeRemain');
    this._countdown($countdown, this.interval);

    this.iId = setInterval(()=> {
      this._countdown($countdown, this.interval);
    }, this.interval * 1000);
  }
    _countdown($countdown, interval){
        let timeRemain = this.timeRemain;
        let days = Math.floor(timeRemain / (60 * 60 * 24));
        let modulo = timeRemain % (60 * 60 * 24);
        let hours = Math.floor(modulo / (60 * 60));
        modulo = modulo % (60 * 60);
        let minutes = Math.floor(modulo / 60);
        let seconds = modulo % 60;
        let context = '';
        context += days ? days + '天' : '';
        context += hours ? hours + '时' : '';
        context += minutes ? minutes + '分' : '';
        context += seconds ? seconds + '秒' : '';
        this.timeRemain = timeRemain - interval;
        $countdown.text(context);
        if (this.timeRemain<=0) {
            $countdown.text('直播已经开始');
            window.clearInterval(this.iId);
        }
    }
}