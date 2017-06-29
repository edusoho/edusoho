import ActivityEmitter from "../activity-emitter";
export default class LiveShow {
  constructor() {
    this._startEvent();
    this._countdownEvent();
  }

  _startEvent() {
      let self = this;
      let emitter = new ActivityEmitter();
      $("#js-start-live").on("click", function () {
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
    this.$countdown = $('#countdown');
    if (this.$countdown.length = 0) return;

    this.timeRemain = this.$countdown.data('timeRemain');
    this._countdown();

    this.iId = setInterval(()=> {
      this._countdown();
    }, 1000);
  }
    _countdown(){
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
        this.timeRemain = timeRemain - 1;
        $('#countdown').text(context);
    }
}