import 'store';

export default class AudioRecorder {
  constructor(container) {
    this.container = container;
    this.interval = 120;
  }

  addAudioPlayerCounter(emitter, player) {
    let $container = $(this.container);
    let activityId = $container.data('id');
    let playerCounter = store.get('activity_id_' + activityId + '_playing_counter');
    if (!playerCounter) {
      playerCounter = 0;
    }
    if (!(player && player.playing)) {
      return false;
    }
    if (playerCounter >= this.interval) {
      emitter.emit('watching', {watchTime: this.interval}).then(() => {
      }).catch((error) => {
        console.error(error);
      });
      playerCounter = 0;
    } else if (player.playing) {
      playerCounter++;
    }
    store.set('activity_id_' + activityId + '_playing_counter', playerCounter);
  }

}

