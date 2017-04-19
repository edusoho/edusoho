import 'store';

export default class VideoRecorder {
  constructor(container) {
    this.container = container;
    this.interval = 120;
  }

  addVideoPlayerCounter(emitter, player) {
    let $container = $(this.container);
    let activityId = $container.data('id');
    let playerCounter = store.get("activity_id_" + activityId + "_playing_counter");
    if (!playerCounter) {
      playerCounter = 0;
    }
    if (!(player && player.playing)) {
      return false;
    }
    if (playerCounter >= this.interval) {
      emitter.emit('watching', {watchTime: this.interval}).then(() => {
        let url = $("#video-content").data('watchUrl');
        $.post(url, function (response) {
          if (response && response.status == 'error') {
            window.location.reload();
          }
        })
      }).catch((error) => {
        console.error(error);
      });
      playerCounter = 0;
    } else if (player.playing) {
      playerCounter++;
    }
    store.set("activity_id_" + activityId + "_playing_counter", playerCounter);
  }

}

