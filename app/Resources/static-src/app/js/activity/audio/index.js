import AudioPlay from './audio';
import AudioRecorder from './audio-recorder';

let recorder = new AudioRecorder('#audio-content');
let audioPlay = new AudioPlay('#audio-content', recorder);
audioPlay.play();