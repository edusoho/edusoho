webpackJsonp(["app/js/activity/audio/index"],[
/* 0 */
/***/ (function(module, exports) {

	import AudioPlay from './audio';
	import AudioRecorder from './audio-recorder';
	
	var recorder = new AudioRecorder('#audio-content');
	var audioPlay = new AudioPlay('#audio-content', recorder);
	audioPlay.play();

/***/ })
]);