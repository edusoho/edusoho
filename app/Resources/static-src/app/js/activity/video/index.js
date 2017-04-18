import VideoRecorder from './video-recorder';
import VideoPlay from './video-play';

let recorder = new VideoRecorder('#video-content');
let videoplay = new VideoPlay(recorder);
videoplay.play();
