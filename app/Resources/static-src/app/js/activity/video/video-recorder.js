import 'store';

export default class VideoRecorder {
  constructor(container) {
    this.container = container;
    this.interval = $(this.container).data('watchTimeSec');
    this.playerCounter = 0;
    this.activityId = $(this.container).data('id');
  }

  addVideoPlayerCounter(emitter, player) {
    let playerCounter = store.get('activity_id_' + this.activityId + '_playing_counter');
    if (!playerCounter) {
      this.playerCounter = 0;
    }
    if (!(player && player.playing)) {
      return false;
    }
    if (playerCounter >= this.interval) {
      this.watching(emitter);
    } else if (player.playing) {
      this.playerCounter++;
    }
    store.set('activity_id_' + this.activityId + '_playing_counter', this.playerCounter);
  }

  watching(emitter) {
    let watchTime = store.get('activity_id_' + this.activityId + '_playing_counter');
    console.log(watchTime);
    emitter.emit('watching', {watchTime: watchTime}).then(() => {
      let url = $('#video-content').data('watchUrl');
      $.post(url, function (response) {
        if (response && response.status == 'error') {
          window.location.reload();
        }
      });
    }).catch((error) => {
      console.error(error);
    });
    this.playerCounter = 0;
  }
}

